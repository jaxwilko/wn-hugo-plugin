<?php foreach ($formValue as $entry): ?>
    <div class="hugo-row">
        <?php if ($entry instanceof \JaxWilko\Hugo\Classes\Test\Actions\CommandResult): ?>
            <div class="hugo-icon hugo-<?= $entry->result->status ?? 'null' ?>-status" title="Command">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 7.5l3 2.25-3 2.25m4.5 0h3m-9 8.25h13.5A2.25 2.25 0 0021 18V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v12a2.25 2.25 0 002.25 2.25z" />
                </svg>
            </div>
            <div class="hugo-content">
                <p><strong><?= strtoupper($entry->command) ?></strong></p>
                <div>
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Key</th>
                            <th>Value</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($entry->args as $key => $value): ?>
                            <?php if (is_array($value) && !isset($value[0])): ?>
                                <?php foreach ($value as $k => $v): ?>
                                    <tr>
                                        <td><?= $k ?></td>
                                        <td><?= is_array($v) ? json_encode($v, JSON_PRETTY_PRINT) : $v ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td><?= $key ?></td>
                                    <td><?= is_array($value) ? json_encode($value, JSON_PRETTY_PRINT) : $value ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($entry->result): ?>
                    <div>
                        <p><strong>Result</strong></p>
                        <table class="table">
                            <thead>
                            <tr>
                                <th>Status</th>
                                <th>Value</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>
                                    <?=
                                        match($entry->result->status) {
                                            \JaxWilko\Hugo\Classes\Test\TestEngine::STATUS_OKAY => 'Okay',
                                            \JaxWilko\Hugo\Classes\Test\TestEngine::STATUS_GENERAL_ERROR => 'General Error',
                                            \JaxWilko\Hugo\Classes\Test\TestEngine::STATUS_UNCAUGHT_ERROR => 'Uncaught Error',
                                            \JaxWilko\Hugo\Classes\Test\TestEngine::STATUS_NO_EXIT_ERROR => 'Exit Error',
                                        }
                                    ?>
                                </td>
                                <td><?= $entry->result->value ?></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        <?php elseif ($entry instanceof \JaxWilko\Hugo\Classes\Test\Actions\Screenshot): ?>
            <div class="hugo-icon" title="Screenshot">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                </svg>
            </div>
            <div class="hugo-content">
                <p><strong><?= $entry->label ?></strong></p>
                <img src="<?= \Illuminate\Support\Facades\Storage::url(str_after($entry->path, 'app/')) ?>" alt="<?= $entry->label ?>">
            </div>
        <?php elseif ($entry instanceof \JaxWilko\Hugo\Classes\Test\Actions\LogEntry): ?>
            <div class="hugo-icon" title="Log Entry">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
                </svg>
            </div>
            <div class="hugo-content">
                <pre>"<?= $entry->message ?>"</pre>
            </div>
        <?php else: ?>
            <?php dump($entry); ?>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

<style>
    .hugo-row {
        width: auto;
        display: block;
        margin: 10px 0;
        padding: 10px;
        border-radius: 3px;
        border: 1px solid #cecece;
        background: #eaeaea;
    }
    .hugo-icon svg {
        width: 20px;
    }
    .hugo-icon {
        padding: 6px 6px 1px 6px;
        width: fit-content;
        display: inline-block;
        box-shadow: -1px 1px 2px 0px #5b5b5b;
        background: #f6f6f6;
        border-radius: 3px;
    }
    .hugo-0-status {
        background: #91e591;
    }
    .hugo-1-status, .hugo-2-status, .hugo-3-status {
        background: #ff9f9f;
    }
    .hugo-content {
        display: inline-block;
        width: 94%;
        margin-left: 10px;
        vertical-align: top;
        margin-top: 5px;
    }
    .hugo-content pre {
        margin: 0;
    }
    .hugo-content img {
        max-width: 100%;
    }
    .hugo-content table {
        background: white;
        border-radius: 4px;
        box-shadow: 0px 2px 4px 0px #b9b9b9;
    }
</style>
