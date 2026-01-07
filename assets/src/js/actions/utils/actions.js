import actionConfig from '~plugin/models/action/action-commands.yaml';

export const parseCommands = (actions) => {
    const commands = [];
    let current;
    for (let i = 0; i < actions.result.length; i++) {
        current = actions.result[i];
        if (
            actions.result[i + 1]
            && actions.result[i + 1]._group === 'screenshot'
            && !actions.result[i + 1].hasOwnProperty('original_index')
        ) {
            current.screenshot = actions.result[i + 1];
            i++;
        }
        commands.push(current);

        // Detected compound result object
        if (current?.result?.results) {
            let subcurrent;
            for (let j = 0; j < current.result.results.length; j++) {
                subcurrent = current.result.results[j];
                subcurrent.original_index = `${current.original_index}-${subcurrent.condition ? 'condition' : current.result.value}-${subcurrent.original_index}`;
                subcurrent.nested = true;
                if (
                    current.result.results[j + 1]
                    && current.result.results[j + 1]._group === 'screenshot'
                    && !current.result.results[j + 1].hasOwnProperty('original_index')
                ) {
                    subcurrent.screenshot = current.result.results[j + 1];
                    j++;
                }
                commands.push(subcurrent);
            }
        }
    }

    return commands;
};

export const getScreenshots = (actions, startedAt, storageUrl) => {
    const screenshots = [];
    actions.forEach((action) => {
        const src = action._group === 'screenshot'
            ? action?.result.value?.path.substring(4)
            : action.screenshot?.result?.value?.path.substring(4)

        if (!src) {
            return;
        }

        screenshots.push({
            src: `${storageUrl}${src}`,
            alt: `${actionConfig[action._group].name} at ${action?.result?.timestamp ? `${(action?.result?.timestamp - startedAt).toFixed(2)}s` : null}`
        })
    });
    return screenshots
};
