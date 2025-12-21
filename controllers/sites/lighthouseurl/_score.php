<?php
$score = $value * 100;
$colour = $score >= 90 ? 'text-green-500' : ($score >= 50 ? 'text-orange-500' : 'text-red-500');
$size = $size ?? 'large';
?>
<div class="relative <?= $size === 'large' ? 'size-12' : 'size-10' ?> mx-auto">
    <svg class="size-full -rotate-90" viewBox="0 0 40 40">
        <circle
            cx="20" cy="20" r="16"
            fill="none"
            class="stroke-current text-white"
            stroke-width="5"
        />
        <circle
            cx="20" cy="20" r="16"
            fill="none"
            class="stroke-current <?= $colour ?> transition-all duration-300"
            stroke-width="5"
            stroke-dasharray="101"
            stroke-dashoffset="<?= 100 - $score ?>"
        />
    </svg>
    <div class="absolute top-1/2 start-1/2 transform -translate-y-1/2 -translate-x-1/2">
        <div class="text-center <?= $size === 'large' ? 'text-lg' : 'text-md' ?> <?= $colour ?>"><?= $score ?></div>
    </div>
</div>
