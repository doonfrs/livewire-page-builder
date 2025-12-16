---
description: 
globs: 
alwaysApply: true
---

# Your rule content

## Design Rules

- Use daisyui
- Use less css classes if possible
- Always take care of dark / light mode
- Please try not to create svg as much as possible, and use blade icons
- We are using tailwind 4, please make sure to not to use deprecated classes / code <https://tailwindcss.com/docs/upgrade-guide#removed-deprecated-utilities> and if you encounter an old code, upgrade it
- I prefer using blade icons instead of direct svgs
- Deprecated Replacement
bg-opacity-*Use opacity modifiers like bg-black/50
text-opacity-* Use opacity modifiers like text-black/50
border-opacity-*Use opacity modifiers like border-black/50
divide-opacity-* Use opacity modifiers like divide-black/50
ring-opacity-*Use opacity modifiers like ring-black/50
placeholder-opacity-* Use opacity modifiers like placeholder-black/50
flex-shrink-*shrink-*
flex-grow-*grow-*
overflow-ellipsis text-ellipsis
decoration-slice box-decoration-slice
decoration-clone box-decoration-clone

## Framework

- We are using laravel 12
- try to avoid writing @php inside livewire views please
- Using Livewire 3, please make sure not to use deprecated method <https://livewire.laravel.com/docs/upgrading#emitup>
- it is a laravel package the main folder is src/ not app/

## Debugging

- This is a package and we are using it inside the shop laravel project, to access the laravel.log you have to connect to the docker trinavo_shop
- The laravel project is in /var/www/html

## Coding Style

- If we want to use specific arguments and keep some to the default, you may consider using named arguments when calling function as var:val, use it wisely not to over use it for any method call.
- Always try to use Auth::user instead of auth()->user if possible.
- Let's add PHPDoc for class variables like /** @var Order $order */ for $order, of course not for built int types.
