<div class="report-widget">
    <h3><?= e($this->property('title')) ?></h3>

    <?php if (!isset($error)): ?>
        <div class="hugo-app">
            <div class="flex flex-col max-w-full overflow-x-auto">
                <?php foreach ($sites as $index => $site): ?>
                    <div class="flex flex-row gap-8 <?= $index < count($sites) - 1 ? 'border-b-2 border-gray-200 mb-6 pb-6' : '' ?>">
                        <div class="whitespace-nowrap w-[200px] mr-auto">
                            <span class="text-lg font-bold uppercase text-gray-600"><a href="<?= \Backend\Facades\Backend::url('jaxwilko/hugo/sites/update/' . $site['id']) ?>"><?= e($site['name']) ?></a></span>
                            <div class="w-[125px] rounded-2xl overflow-hidden my-2">
                                <a href="<?= \Backend\Facades\Backend::url('jaxwilko/hugo/sites/update/' . $site['id']) ?>">
                                    <img src="<?= e($site['image']['path']) ?>" alt="<?= e($site['name']) ?>" class="transition-transform duration-300 transform hover:scale-110">
                                </a>
                            </div>
                        </div>
                        <div>
                            <span class="text-lg font-bold uppercase text-gray-600">Status</span>
                            <div class="py-4">
                                <?php if (!$site['is_down']): ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#0cce6b" class="size-24" title="Site is online">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                <?php else: ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ff4e42" class="size-24" title="Site is offline">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                    </svg>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($performance): ?>
                            <div>
                                <span class="text-lg font-bold uppercase text-gray-600">Lighthouse</span>
                                <div class="flex gap-8 my-2">
                                    <?php foreach (['performance', 'accessibility', 'best_practice', 'seo'] as $property): ?>
                                        <div class="flex flex-col text-center">
                                            <svg width="92" height="92" viewBox="0 0 100 100">
                                                <circle class="stroke-blue-400/20" cx="50" cy="50" r="45" stroke-width="10" fill="none"></circle>
                                                <circle cx="50" cy="50" r="45" stroke-width="10" fill="none" stroke="<?= e($site['score_' . $property] >= 0.9 ? '#0cce6b' : ($site['score_' . $property] >= 0.5 ? '#ffa400' : '#ff4e42')) ?>" stroke-linecap="round" stroke-dasharray="282.7433388230814" stroke-dashoffset="<?= e(abs(282.743 * (1 - ($site['score_' . $property])))) ?>" class="origin-center -rotate-90 transition-all duration-700 ease-out"></circle>
                                                <text x="50" y="50" text-anchor="middle" dominant-baseline="central" class="font-bold text-gray-900 text-[26px]"><?= e($site['score_' . $property] * 100) ?></text>
                                            </svg>
                                            <span class="whitespace-nowrap py-2"><?= e(strtoupper(str_replace('_', ' ', $property))) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="w-full">
                            <?php if (isset($actions)): ?>
                                <?php $siteActions = array_filter($actions, fn ($a) => $a['site_id'] === $site['id']); ?>
                                <?php if ($siteActions): ?>
                                    <span class="text-lg font-bold uppercase text-gray-600">Actions</span>
                                    <div class="w-full max-h-[150px] overflow-y-auto">
                                        <table class="table table-responsive w-full border-separate whitespace-nowrap border-transparent">
                                            <thead>
                                                <tr>
                                                    <th class="border-none pb-2">Action</th>
                                                    <th class="text-center border-none pb-2">Status</th>
                                                    <th class="text-center border-none pb-2">Last Ran</th>
                                                    <th class="text-center border-none pb-2"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($siteActions as $action): ?>
                                                    <tr>
                                                        <td class="p-2"><?= e($action['name']) ?></td>
                                                        <td class="text-center p-2">
                                                            <?php if (!$action['status']): ?>
                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#0cce6b" class="size-6 mx-auto">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                                </svg>
                                                            <?php else: ?>
                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ff4e42" class="size-6 mx-auto">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                                                </svg>
                                                            <?php endif; ?>
                                                        </td>
                                                        <?php $date = \Carbon\Carbon::createFromTimeString($action['created_at']); ?>
                                                        <td class="text-center p-2" title="<?= e($date->format('Y-m-d H:i:s')) ?>"><?= e($date->diffForHumans()) ?></td>
                                                        <td class="text-center p-2"><a href="<?= \Backend\Facades\Backend::url('/jaxwilko/hugo/workflowresults/update/' . $action['workflow_result_id']) ?>">View</a></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php else: ?>
        <p class="flash-message static warning"><?= e($error) ?></p>
    <?php endif ?>
</div>

