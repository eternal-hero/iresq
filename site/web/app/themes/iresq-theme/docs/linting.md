# Linting and VSCode Configuration

Sage 9 comes with `eslint` and `stylelint` configurations. You can edit those files to meet your needs but you should not just delete them altogether. It can be a lot to keep track of errors just by unning `yarn lint`, but luckily VSCode has some extensions that will help with this.

1. First you need to download the extensions [ESLint](https://marketplace.visualstudio.com/items?itemName=dbaeumer.vscode-eslint), and [StyleLint](https://marketplace.visualstudio.com/items?itemName=stylelint.vscode-stylelint).
2. Now go into settings (`CMD + ,`) and type `eslint`. Make sure it's enabled. Now do the same in the settings for `styelint`.
3. Reload VSCode.
4. You should now get warnings in your console if you have any style errors. To keep yourself from having to manually fix everything, go into your VSCode settings again (`CMD + ,`) and type `format on save`. Enable it and now your code should format itself every time you save the file.
