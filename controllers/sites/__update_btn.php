<hugo-button icon="edit" title="Update Lighthouse Url" onclick="<?= sprintf(
    "$.wn.relationBehavior.clickViewListRecord('%s', '%s', '%s')",
    $value,
    $this->relationGetId(),
    $this->relationGetSessionKey()
) ?>"></hugo-button>
