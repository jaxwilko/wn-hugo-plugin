<template>
    <div>
        <HeadlineScores :report="lighthouse"></HeadlineScores>
        <div class="flex flex-col mx-20">
            <div class="my-8 border-t border-blue-200"></div>
            <div class="flex flex-row">
                <div class="w-full flex flex-col mx-auto">
                    <Score :score="lighthouse.score_performance" size="xl"></Score>
                    <span class="font-bold text-xl mt-3 mx-auto">Performance</span>
                </div>
                <div class="w-full flex mx-auto">
                    <LightboxImage :src="lighthouse.final_image"
                                   :fullsize-src="lighthouse.full_page_image"
                                   alt="Lighthouse final image"
                                   class="ml-auto"
                    ></LightboxImage>
                </div>
            </div>
            <div class="border-t border-blue-200 pt-8 mt-8">
                <div class="w-full flex flex-row gap-2">
                    <div v-for="snapshot in timeline">
                        <LightboxImage :src="snapshot.src" :alt="snapshot.alt" :group="timeline"></LightboxImage>
                        <span>{{snapshot.label}}</span>
                    </div>
                </div>
            </div>
            <AuditOverview :audits="performanceScoreAudits"></AuditOverview>
            <AuditDetails :audits="performanceAudits"></AuditDetails>

            <div class="my-8 border-t border-blue-200"></div>
            <div class="w-full flex flex-col mx-auto">
                <Score :score="lighthouse.score_accessibility" size="xl"></Score>
                <span class="font-bold text-xl mt-3 mx-auto">Accessibility</span>
            </div>
            <AuditDetails :audits="accessibilityAudits"></AuditDetails>

            <div class="my-8 border-t border-blue-200"></div>
            <div class="w-full flex flex-col mx-auto">
                <Score :score="lighthouse.score_best_practice" size="xl"></Score>
                <span class="font-bold text-xl mt-3 mx-auto">Best Practice</span>
            </div>
            <AuditDetails :audits="bestPracticeAudits"></AuditDetails>

            <div class="my-8 border-t border-blue-200"></div>
            <div class="w-full flex flex-col mx-auto">
                <Score :score="lighthouse.score_seo" size="xl"></Score>
                <span class="font-bold text-xl mt-3 mx-auto">SEO</span>
            </div>
            <AuditDetails :audits="seoAudits"></AuditDetails>
        </div>
    </div>
</template>
<script>
import Score from '~plugin/assets/src/js/sites/components/Score.vue';
import HeadlineScores from '~plugin/assets/src/js/sites/components/HeadlineScores.vue';
import AuditIcon from '~plugin/assets/src/js/sites/components/audit/AuditIcon.vue';
import AuditOverview from '~plugin/assets/src/js/sites/components/audit/AuditOverview.vue';
import AuditInsights from '~plugin/assets/src/js/sites/components/audit/AuditInsights.vue';
import LightboxImage from '~plugin/assets/src/js/components/LightboxImage.vue';

export default {
    name: 'Report',
    props: ['lighthouse'],
    components: {LightboxImage, AuditDetails: AuditInsights, AuditOverview, AuditIcon, HeadlineScores, Score},
    data: () => {
        return {
            performanceDetailed: false,
        };
    },
    computed: {
        performanceScoreAudits() {
            return this.getWeightedAudits('performance');
        },
        performanceAudits() {
            return this.getAudits('performance');
        },
        accessibilityAudits() {
            return this.getAudits('accessibility');
        },
        bestPracticeAudits() {
            return this.getAudits('best-practices');
        },
        seoAudits() {
            return this.getAudits('seo');
        },
        timeline() {
            const timeline = [];
            Object.entries(this.lighthouse.timeline).forEach(([key, value]) => {
                timeline.push({
                    label: key,
                    alt: `Site at ${key}ms`,
                    src: value,
                })
            });
            return timeline;
        }
    },
    methods: {
        getAudits(category) {
            const audits = [];
            this.lighthouse.report.categories[category].auditRefs.forEach((auditRef) => {
                if (!this.lighthouse.report.audits[auditRef.id] || (category === 'performance' && auditRef.weight)) {
                    return;
                }
                audits.push({
                    id: auditRef.id,
                    weight: auditRef.weight,
                    data: this.lighthouse.report.audits[auditRef.id]
                });
            });

            return audits.sort((a, b) => a.weight - b.weight);
        },
        getWeightedAudits(category) {
            const audits = [];
            this.lighthouse.report.categories[category].auditRefs.forEach((auditRef) => {
                if (!auditRef.weight || !this.lighthouse.report.audits[auditRef.id]) {
                    return;
                }

                audits.push({
                    id: auditRef.id,
                    weight: auditRef.weight,
                    data: this.lighthouse.report.audits[auditRef.id]
                });
            });

            return audits.sort((a, b) => a.weight - b.weight);
        },
        parseMarkdownUrls(str) {
            const match = str.match(/\[(.*)\]\((.*)\)/);

            if (match.length !== 3) {
                return str;
            }

            return str.replace(match[0], `<a href="${match[2]}" target="_blank">${match[1]}</a>`);
        },
        scoreColour(score) {
            if (score < 0.5) {
                return 'text-red-600';
            }

            if (score < 0.9) {
                return 'text-orange-600';
            }

            return 'text-green-600';
        }
    }
};
</script>
