<?php
$score = $value * 100;
$id = \Str::random(16);
?>

<div style="display: block">
    <svg
        id="svg-<?= $id ?>"
        class="score-circle"
        width="30"
        height="30"
        data-score="<?= $score ?>"
    >
        <circle
            class="outer"
            stroke="rgba(0, 0, 0, 0)"
            stroke-width="3"
            fill="transparent"
            r="12"
            cx="15"
            cy="15"
        />
        <circle
            class="score"
            <?php if ($score >= 90): ?>
                stroke="#4caf50"
            <?php elseif ($score >= 50): ?>
                stroke="#ff9800"
            <?php else: ?>
                stroke="#f44336"
            <?php endif; ?>
            stroke-width="3"
            fill="transparent"
            r="12"
            cx="15"
            cy="15"
            />
            <?php if (!in_array($score, [0, 100])): ?>
                <text
                    <?php if ($score >= 90): ?>
                        fill="#4caf50"
                    <?php elseif ($score >= 50): ?>
                        fill="#ff9800"
                    <?php else: ?>
                        fill="#f44336"
                    <?php endif; ?>
                    y="19"
                    x="8"
                    style="font-size: 12px"
                    >
                    <?= $score ?>
                </text>
            <?php endif; ?>
    </svg>
    <script>
        (() => {
            const element = document.querySelector("#svg-<?= $id ?>");
            const circle = element.querySelector('circle.score');
            const circumference = circle.r.baseVal.value * 2 * Math.PI;

            circle.style.strokeDasharray = `${circumference} ${circumference}`;
            circle.style.strokeDashoffset = circumference - element.getAttribute("data-score") / 100 * circumference;
        })();
    </script>
</div>
