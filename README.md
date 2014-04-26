# M12.Utils

Extra helpers / libraries used by other M12.* Neos plugins.

### M12.Utils:WrapRemover

Removes extra DIV wrapper - by definition needed by Neos back-end (both those around Conent Collection or around any other Node Type) - but not really necessary when in front-end (live) workspace.

This helper only works when exactly `<div class="[wrapperClass]">[content]</div>` is found around element - if extra attributes or classes were added, it won't run. Also, if you changed default class around Neos NodeTypes (defined for `TYPO3.Neos:Content` in `attributes.class.@process.nodeType`, you'll have to adjust `.wrapperClass` accordingly.

Example usage:

To remove wrapper `<div class="neos-contentcollection">...</div>`, add to your TypoScript:
```
prototype(TYPO3.Neos:ContentCollection) {
	@process.wrapRemover = M12.Utils:WrapRemover
	@process.wrapRemover.wrapperClass = 'neos-contentcollection'
}
```

To remove wrapper e.g. around Text element `<div class="typo3-neos-nodetypes-text">...</div>`, add to your TypoScript:
```
prototype(TYPO3.Neos.NodeTypes:Text) {
	@process.wrapRemover = M12.Utils:WrapRemover
	@process.wrapRemover.wrapperClass = 'typo3-neos-nodetypes-text'
}
```

## Installation

It's defined as `typo3-flow-plugin`, hence needs to be placed in `/Packages/Plugins/` directory. Do this manually by clonning this repository there or add it as a dependency to your site package:

composer.json:
```
    "repositories": [
        { "type": "git", "url": "https://github.com/million12/M12.Utils.git" }
    ],
    "require": {
        "your/dependencies": "*",
        "m12/utils": "*"
    },
```
And then run composer update.
