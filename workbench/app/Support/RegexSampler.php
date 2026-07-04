<?php

namespace Workbench\App\Support;

/**
 * Parse a regex body into a lightweight AST and sample a random matching string.
 *
 * Supports the subset used by config/phones.php patterns: alternation `|`,
 * groups `()`, character classes `[..]` with ranges, `\d`, quantifiers `{n}`
 * and `{n,m}`, and literal digits. Named group headers `(?<name>` are
 * normalised to plain `(` before parsing.
 */
final class RegexSampler
{
    private string $src;

    private int $pos = 0;

    /** @var array<string, mixed> */
    private array $ast;

    public function __construct(string $pattern)
    {
        $this->src = str_replace(['(?<provider>', '(?<digits>'], '(', $pattern);
        $this->ast = $this->parseAlt();
    }

    /**
     * Produce a random string matching the pattern.
     */
    public function sample(): string
    {
        return $this->emit($this->ast);
    }

    /** @return array<string, mixed> */
    private function parseAlt(): array
    {
        $branches = [$this->parseSeq()];
        while ($this->peek() === '|') {
            $this->pos++;
            $branches[] = $this->parseSeq();
        }

        return count($branches) === 1 ? $branches[0] : ['t' => 'alt', 'b' => $branches];
    }

    /** @return array<string, mixed> */
    private function parseSeq(): array
    {
        $items = [];
        while (true) {
            $ch = $this->peek();
            if ($ch === null || $ch === '|' || $ch === ')') {
                break;
            }
            $items[] = $this->parseRepeat();
        }

        return ['t' => 'seq', 'i' => $items];
    }

    /** @return array<string, mixed> */
    private function parseRepeat(): array
    {
        $atom = $this->parseAtom();
        if ($this->peek() === '{') {
            $this->pos++;
            $num = '';
            while (ctype_digit((string) $this->peek())) {
                $num .= $this->src[$this->pos++];
            }
            $min = (int) $num;
            $max = $min;
            if ($this->peek() === ',') {
                $this->pos++;
                $num2 = '';
                while (ctype_digit((string) $this->peek())) {
                    $num2 .= $this->src[$this->pos++];
                }
                $max = $num2 === '' ? $min + 3 : (int) $num2;
            }
            $this->pos++; // }

            return ['t' => 'rep', 'n' => $atom, 'min' => $min, 'max' => $max];
        }

        return $atom;
    }

    /** @return array<string, mixed> */
    private function parseAtom(): array
    {
        $ch = $this->peek();
        if ($ch === '(') {
            $this->pos++;
            $node = $this->parseAlt();
            $this->pos++; // )

            return $node;
        }
        if ($ch === '[') {
            return $this->parseClass();
        }
        if ($ch === '\\') {
            $this->pos++;
            $esc = $this->src[$this->pos++];
            if ($esc === 'd') {
                return ['t' => 'digit'];
            }

            return ['t' => 'lit', 'ch' => $esc];
        }
        $this->pos++;

        return ['t' => 'lit', 'ch' => $ch];
    }

    /** @return array<string, mixed> */
    private function parseClass(): array
    {
        $this->pos++; // [
        $chars = [];
        while (($c = $this->peek()) !== null && $c !== ']') {
            $this->pos++;
            $next = $this->peek();
            $after = $this->pos + 1 < strlen($this->src) ? $this->src[$this->pos + 1] : null;
            if ($next === '-' && $after !== null && $after !== ']') {
                $this->pos++; // -
                $end = $this->src[$this->pos++];
                for ($o = ord($c); $o <= ord($end); $o++) {
                    $chars[] = chr($o);
                }
            } else {
                $chars[] = $c;
            }
        }
        $this->pos++; // ]
        $digits = array_values(array_filter($chars, 'ctype_digit'));

        return ['t' => 'class', 'c' => $digits === [] ? $chars : $digits];
    }

    private function peek(): ?string
    {
        return $this->pos < strlen($this->src) ? $this->src[$this->pos] : null;
    }

    /**
     * @param  array<string, mixed>  $node
     */
    private function emit(array $node): string
    {
        switch ($node['t']) {
            case 'alt':
                return $this->emit($node['b'][mt_rand(0, count($node['b']) - 1)]);
            case 'seq':
                $s = '';
                foreach ($node['i'] as $it) {
                    $s .= $this->emit($it);
                }

                return $s;
            case 'rep':
                $n = mt_rand($node['min'], $node['max']);
                $s = '';
                for ($k = 0; $k < $n; $k++) {
                    $s .= $this->emit($node['n']);
                }

                return $s;
            case 'class':
                return (string) $node['c'][mt_rand(0, count($node['c']) - 1)];
            case 'digit':
                return (string) mt_rand(0, 9);
            case 'lit':
            default:
                return $node['ch'];
        }
    }
}
