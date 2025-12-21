<template>
    <div id="winter-log-viewer">
        <div class="formatted">
            <div>
                <div class="btn-group" role="group">
                    <button disabled class="btn btn-sm btn-secondary">Context</button>
                    <button disabled class="btn btn-sm btn-primary">
                        {{ report.environment.context }}
                    </button>
                </div>

                <div class="btn-group" role="group">
                    <button disabled class="btn btn-sm btn-secondary">Environment</button>
                    <button disabled class="btn btn-sm btn-primary">
                        {{ report.environment.env }}
                    </button>
                </div>

                <div class="btn-group" role="group">
                    <button disabled class="btn btn-sm btn-secondary">Testing</button>
                    <button disabled class="btn btn-sm btn-primary">
                        {{ report.environment.testing ? 'true' : 'false' }}
                    </button>
                </div>

                <div class="my-8 border-t border-blue-200"></div>
            </div>

            <!-- EXCEPTION LIST -->
            <div
                class="exception-list"
            >
                <div
                    v-for="(exception, index) in lighthouseException"
                    :key="index"
                    class="exception"
                >
                    <h1 class="mb-6 text-xl">{{ exception.message.message }}</h1>

                    <pre class="border border-blue-100 rounded-xl bg-white/30 p-6 mb-6">{{ exception.message.errorStack }}</pre>

                    <div>
                        <div class="btn-group">
                            <button disabled class="btn btn-sm btn-secondary">Exception</button>
                            <button disabled class="btn btn-sm btn-primary">
                                #{{ index }}
                            </button>
                        </div>

                        <div class="btn-group">
                            <button disabled class="btn btn-sm btn-secondary">Code</button>
                            <button disabled class="btn btn-sm btn-primary">
                                {{ exception.code }}
                            </button>
                        </div>
                    </div>

                    <!-- SOURCE -->
                    <div class="trace">
                        <div class="trace-frame">
                            <div class="label">
                                <span class="item">{{ exception.file }}</span>
                                at line <span class="item">{{ exception.line }}</span>
                            </div>

                            <div
                                v-if="exception.snippet"
                                class="snippet-preview-container"
                            >
                                <div
                                    class="snippet-preview"
                                    v-html="makeSnippet(exception.snippet, exception.file, exception.line)"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- STACK TRACE -->
                    <span class="trace-title">Stack Trace</span>
                    <div class="trace">
                        <div
                            v-for="(frame, traceIndex) in exception.trace"
                            :key="traceIndex"
                            class="trace-frame"
                        >
                            <div
                                class="label"
                                @click="toggleFrame($event)"
                            >
                                <span class="item">
                                  #{{ traceIndex }} {{ frame.file }}
                                </span>
                                in
                                <span class="item">
                                  {{ frame.class && !frame.function.includes('{') ? frame.class + '::' : '' }}
                                  {{ frame.function }}
                                </span>
                                <span v-if="frame.line">
                                  at line <span class="item">{{ frame.line }}</span>
                                </span>

                                <span v-if="frame.in_app" class="app-icon">In App</span>
                            </div>

                            <div
                                v-if="frame.snippet"
                                class="snippet-preview-container"
                                :class="{ folded: !frame.in_app }"
                            >
                                <div
                                    class="snippet-preview"
                                    v-html="makeSnippet(frame.snippet, frame.file, frame.line)"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'Exception',
    props: ['report'],
    data() {
        return {
            sortOrder: 'old',
        };
    },
    computed: {
        lighthouseException() {
            const exception = this.report.exception;
            if (typeof exception.message === 'string') {
                exception.message = JSON.parse(exception.message);
            }
            return [exception];
        },
    },
    methods: {
        toggleFrame(event) {
            const container = event.currentTarget.nextElementSibling;
            container?.classList.toggle('folded');
        },
        makeSnippet(snippet, file, highlight) {
            return Object.entries(snippet).map(([index, line]) => {
                const lineNumber = parseInt(index) + 1;
                return `<div class="preview-line ${lineNumber === highlight ? 'highlight' : ''}"><span class="line-number">${lineNumber}</span>: ${this.phpSyntaxHighlight(this.escape(line))}</div>`;
            })
            .join('\n');
        },
        phpSyntaxHighlight(str) {
            const regexes = {
                number: {
                    pattern: /(=\(\s)?(\d+)(?=(\s|;|,|\)|=|))/g,
                    replace: "$2",
                    before: (s) => s.replace(/&#039;/g, "'"),
                    after: (s) => s.replace(/'/g, "&#039;"),
                },
                string: {
                    pattern: /(\x221[^\x221]*\x221|\x222[^\x222]*\x222)/g,
                    before: (s) => s.replace(/&#039;/g, "\x221").replace(/&quot;/g, "\x222"),
                    after: (s) => s.replace(/\x221/g, "&#039;").replace(/\x222/g, "&quot;"),
                },
                control: /\b(for|foreach|while|class |extends|yield from|yield|echo|fn|implements|try|catch|finally|throw|new|instanceof| parent|final|function|return|unset|static|public|protected|private|count|global|if|else|else if|intval|int|array)\b/g,
                bool: /(\bnull\b|\btrue\b|\bfalse\b)/g,
                bracket: /(\(|\)|\[|\]|\{|\})/g,
                variable: /(\$[a-z]\w*)/g,
            };

            // Comment detection (single-line only, like the PHP version)
            if (/^\s*(\*|\*\/|\/\*|\/\/|#)/.test(str)) {
                return `<span class="comment">${str}</span>`;
            }

            str = str.replace(/&#62;/g, '>');

            for (const [label, regex] of Object.entries(regexes)) {
                if (regex instanceof RegExp) {
                    str = str.replace(
                        regex,
                        `<span class="${label}">$1</span>`
                    );
                    continue;
                }

                const beforeStr = regex.before ? regex.before(str) : str;

                str = beforeStr.replace(
                    regex.pattern,
                    `<span class="${label}">${regex.replace ?? "$1"}</span>`
                );

                if (regex.after) {
                    str = regex.after(str);
                }
            }

            return str;
        },
        escape(str) {
            return str
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
        },
    },
};
</script>
