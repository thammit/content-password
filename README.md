# Content Password TYPO3 extension


## Usage with Fluid Styled Content

`lib.stdheader` is not available in fluid-styled-content >v8, therefore you may use:

```
tt_content.gridelements_pi1.20.10.setup {
        content_password {
                # fluid_styled_content compatible header
                preCObject =< lib.contentElement
                preCObject.templateName = Header
        }
}
```
