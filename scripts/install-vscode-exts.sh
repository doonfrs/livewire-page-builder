#! /bin/bash
code --install-extension shufo.vscode-blade-formatter
code --install-extension open-southeners.laravel-pint
code --install-extension onecentlin.laravel-blade
code --install-extension adrianwilczynski.alpine-js-intellisense

# check if Cursor is installed
if command -v Cursor &>/dev/null; then
    Cursor --install-extension shufo.vscode-blade-formatter
    Cursor --install-extension open-southeners.laravel-pint
    Cursor --install-extension onecentlin.laravel-blade
    Cursor --install-extension adrianwilczynski.alpine-js-intellisense
fi
