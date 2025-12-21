<template>
    <div class="inline-block relative mx-auto" :style="{ width: sizePx + 'px', height: sizePx + 'px' }" role="img">
        <svg :width="sizePx" :height="sizePx" viewBox="0 0 100 100">
            <!-- Background circle -->
            <circle
                class="stroke-blue-400/20"
                cx="50"
                cy="50"
                :r="radius"
                :stroke-width="strokeWidth"
                fill="none"
            />

            <!-- Foreground (score) circle -->
            <circle
                cx="50"
                cy="50"
                :r="radius"
                :stroke-width="strokeWidth"
                fill="none"
                :stroke="colour"
                stroke-linecap="round"
                :stroke-dasharray="circumference"
                :stroke-dashoffset="dashOffset"
                class="origin-center -rotate-90 transition-all duration-700 ease-out"
            />

            <!-- Numeric score -->
            <text
                x="50"
                y="50"
                text-anchor="middle"
                dominant-baseline="central"
                :class="`font-bold text-gray-900 ${size === 'xs' ? 'text-[34px]' : 'text-[26px]'}`"
            >
                {{ displayScore }}
            </text>

            <!-- Label -->
            <text
                v-if="label"
                x="50"
                y="70"
                text-anchor="middle"
                class="font-medium text-gray-900 text-[9px]"
            >
                {{ label }}
            </text>
        </svg>
    </div>
</template>

<script>
export default {
    name: 'Score',
    props: ['score', 'size', 'label'],
    data() {
        return {
            dashOffset: 300
        }
    },
    computed: {
        displayScore() {
            return Math.round(this.score * 100);
        },
        radius() {
            return 50 - this.strokeWidth / 2
        },
        strokeWidth() {
            return {
                'xl': 10,
                'large': 10,
                'small': 10,
                'xs': 12,
            }[this.size || 'large'];
        },
        sizePx() {
            return {
                'xl': 148,
                'large': 92,
                'small': 48,
                'xs': 36,
            }[this.size || 'large'];
        },
        circumference() {
            return Math.PI * 2 * this.radius
        },
        colour() {
            if (this.displayScore >= 90) {
                return "#0cce6b"
            }

            if (this.displayScore >= 50) {
                return "#ffa400"
            }

            return "#ff4e42"
        },
    },
    watch: {
        displayScore: {
            immediate: true,
            handler(newScore) {
                setTimeout(() => {
                    const pct = Math.max(0, Math.min(100, newScore)) / 100
                    this.dashOffset = + (this.circumference * (1 - pct)).toFixed(3)
                }, 25);
            }
        }
    }
}
</script>
