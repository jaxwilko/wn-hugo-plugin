<?php

namespace JaxWilko\Hugo\Traits;

trait HasHugoProgressBar
{
    protected int $progressBarSize = 30;

    public function progressBar(array $items, ?string $label, callable $callback): void
    {
        echo "\033[?25l"; // hide cursor

        $count = count($items);
        $items = array_values($items);
        $max = 0;

        foreach ($items as $index => $item) {
            $str = $this->makeBar($index, $count, $label ? (is_array($item) ? $item[$label] : $item->{$label}) : null);

            $len = strlen($str);
            $max = max($max, $len);

            echo $str . str_repeat(' ', $max - $len) . "   \r";

            $callback($item);
        }

        $str = $this->makeBar($count, $count, 'Complete');

        echo $str . str_repeat(' ', $max - strlen($str)) . PHP_EOL;

        echo "\033[?25h"; // show cursor
    }

    protected function makeBar(int $current, int $total, ?string $label = null): string
    {
        $percentage = ($current / $total) * $this->progressBarSize;

        return sprintf(
            " %d/%d \e[0;34m%s\e[0;37m%s\e[0m%s",
            $current + ($current !== $total),
            $total,
            str_repeat('▓', $percentage),
            str_repeat('░', $this->progressBarSize - $percentage),
            $label ? ' - ' . $label : ''
        );
    }

    public function handleCleanup(): void
    {
        echo PHP_EOL;
        echo "\033[?25h"; // show cursor
    }
}
