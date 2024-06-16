<?php

namespace Autumn\System\ClassFactory;

class DocComment implements \Stringable
{
    public const TAB = "\t";

    public const DEFAULT_LINE_WIDTH = 80;

    private array $annotations = [];

    public function __construct(
        private string $comment = '',
        array $annotations = [],
        private string $intent = '',
        private int $lineWidth = self::DEFAULT_LINE_WIDTH
    ) {
        foreach ($annotations as $name => $annotation) {
            if ($annotation instanceof Annotation) {
                if (is_string($name)) {
                    $annotation->setName($name);
                }
                $this->annotations[] = $annotation;
            } elseif (is_array($annotation)) {
                $this->addAnnotation($name, ...$annotation);
            } elseif (isset($annotation)) {
                if ($name === 'deprecated') {
                    $this->setDeprecated($annotation);
                } else {
                    $this->addAnnotation($name, (string)$annotation);
                }
            }
        }
    }

    public static function parse(string $comment): DocComment
    {
        if (preg_match('/^(?<intent>\s*)\/\*/m', $comment, $matches)) {
            $pos = strpos($comment, $matches[0]);
            $comment = substr($comment, $pos + strlen($matches[0]));
            $comment = ltrim($comment, "* \n\r\t\v\x00");
            if (str_starts_with($comment, '@')) {
                $comment = '* ' . $comment;
            }
            $intent = $matches['intent'];
            $minIntentLen = 0;
        } else {
            $intent = '';
            $minIntentLen = self::DEFAULT_LINE_WIDTH;
        }

        $instance = new static(intent: $intent);

        $lines = preg_split('/\R+/', $comment);
        foreach ($lines as $line) {
            if (preg_match('/\*\/\s*$/', $line)) {
                break;
            }

            $instance->lineWidth = max($instance->lineWidth, strlen($line));

            if (preg_match('/^\s*\*\s*(.*)$/', $line, $matches)) {
                $line = $matches[1];
                if (preg_match('/@(\w+)(\s+.*)?$/', $line, $matches)) {
                    $instance->annotations[] = $annotation = new Annotation($matches[1], trim($matches[2] ?? ''));
                    continue;
                }
            }

            $comment = ltrim($line);
            if ($minIntentLen > 0) {
                $minIntentLen = min($minIntentLen, strlen($line) - strlen($comment));
            }

            if (isset($annotation)) {
                $annotation->setContent($annotation->getContent() . "\n" . rtrim($comment));
            } else {
                $instance->comment .= rtrim($comment) . "\n";
            }
        }

        if ($minIntentLen > 0) {
            $instance->intent = str_repeat(' ', $minIntentLen);
        }

        $instance->comment = rtrim($instance->comment);

        return $instance;
    }

    public static function print(int|string $intent, ?self $docComment, mixed ...$others): string
    {
        if (is_int($intent)) {
            $intent = str_repeat(static::TAB, $intent);
        }

        $lines = [];
        if ($docComment) {
            $docComment->intent = $intent;
            $lines[] = $docComment;
        }

        foreach ($others as $other) {
            $lines[] = $intent . $other;
        }

        return implode(PHP_EOL, $lines);
    }

    public function __toString(): string
    {
        $intent = $this->intent ?? '';
        $lines = [$intent . '/**'];

        if (!empty($this->comment)) {
            if ($this->lineWidth > 0) {
                // Calculate the effective line width considering the indent and the comment markers
                $effectiveLineWidth = $this->lineWidth - strlen($intent) - 3; // 3 accounts for ' * '
                foreach (explode("\n", wordwrap($this->comment, $effectiveLineWidth)) as $line) {
                    $lines[] = $intent . ' * ' . $line;
                }
            } else {
                foreach (explode(PHP_EOL, $this->comment) as $line) {
                    $lines[] = $intent . ' * ' . $line;
                }
            }

            if (!empty($this->annotations)) {
                $lines[] = $intent . ' * ';
            }
        }

        foreach ($this->annotations as $annotation) {
            $lines[] = $intent . ' * ' . $annotation;
        }

        $lines[] = $intent . ' */';

        return implode(PHP_EOL, $lines);

        // $lines = [$this->intent . '/**'];

        // foreach (explode(PHP_EOL, $this->comment) as $line) {
        //     array_push($lines, ...$this->breakLine($line, $this->lineWidth, $this->intent . ' * '));
        // }

        // foreach ($this->annotations as $annotation) {
        //     array_push($lines, ...$this->breakLine((string)$annotation, $this->lineWidth, $this->intent . ' * '));
        // }

        // $lines[] = $this->intent . ' */';

        // return implode(PHP_EOL, $lines);
    }

    // public function breakLine(string $line, int $lineWidth = null, string $intent = null): array
    // {
    //     if (!$lineWidth || $lineWidth < 1) {
    //         return [$intent . $line];
    //     }

    //     $prefix = $intent ?: '';
    //     if (strlen($prefix) >= $lineWidth) {
    //         throw new \InvalidArgumentException('The text intent is too long.');
    //     }

    //     $pattern = preg_quote(",.?!- \r\n\t\v\0", '#');
    //     $pattern = "#[$pattern][^$pattern]*$#";

    //     $parts = [];
    //     $line = $intent . $line;

    //     while ($chopped = substr($line, 0, $lineWidth + 1)) {
    //         if (preg_match($pattern, $chopped, $matches)) {
    //             if ($matched = $matches[0]) {
    //                 if ($part = substr($chopped, 0, -strlen($matched) + 1)) {
    //                     $chopped = $part;
    //                 }
    //             }
    //         }

    //         $parts[] = rtrim($chopped);
    //         if ($line = substr($line, strlen($chopped))) {
    //             $line = $intent . ltrim($line);
    //         } else {
    //             break;
    //         }
    //     }

    //     return $parts;
    // }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }

    public function getAnnotations(): array
    {
        return $this->annotations;
    }

    public function addAnnotation(string $name, string|null ...$notes): void
    {
        $this->annotations[] = new Annotation($name, trim(implode(" ", $notes)));
    }

    public function appendAnnotation(Annotation ...$annotations): void
    {
        array_push($this->annotations, ...$annotations);
    }

    public function getIntent(): string
    {
        return $this->intent;
    }

    public function setIntent(string $intent): void
    {
        $this->intent = $intent;
    }

    public function getLineWidth(): int
    {
        return $this->lineWidth;
    }

    public function setLineWidth(int $lineWidth): void
    {
        $this->lineWidth = $lineWidth;
    }

    public function isDeprecated(): bool
    {
        return $this->getDeprecated() !== false;
    }

    public function getDeprecated(): bool|string
    {
        foreach ($this->annotations as $annotation) {
            if ($annotation->getName() === 'deprecated') {
                return $annotation->getContent() ?: true;
            }
        }

        return false;
    }

    public function setDeprecated(string|bool $since): void
    {
        if ($since === false) {
            foreach ($this->annotations as $key => $annotation) {
                if ($annotation->getName() === 'deprecated') {
                    unset($this->annotations[$key]);
                }
            }
        } else {
            if ($since === true) {
                $since = '';
            }

            $found = false;
            foreach ($this->annotations as $key => $annotation) {
                if ($annotation->getName() === 'deprecated') {
                    if ($found) {
                        unset($this->annotations[$key]);
                    } else {
                        $annotation->setContent($since);
                        $found = true;
                    }
                }
            }

            if (!$found) {
                $this->annotations[] = new Annotation('deprecated', $since);
            }
        }
    }

    public function addParameter(Parameter $parameter): void
    {
        $this->addAnnotation('param', $parameter->getType(), '$' . $parameter->getName(), $parameter->getComment());
    }
}
