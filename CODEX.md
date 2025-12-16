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

### Dynamic @container
- we use dynamic https://tailwindcss.com/docs/responsive-design#container-queries 
- we use https://tailwindcss.com/docs/responsive-design#container-size-reference
Container size reference
```
Variant	Minimum width	CSS
@3xs	16rem (256px)	@container (width >= 16rem) { … }
@2xs	18rem (288px)	@container (width >= 18rem) { … }
@xs	20rem (320px)	@container (width >= 20rem) { … }
@sm	24rem (384px)	@container (width >= 24rem) { … }
@md	28rem (448px)	@container (width >= 28rem) { … }
@lg	32rem (512px)	@container (width >= 32rem) { … }
@xl	36rem (576px)	@container (width >= 36rem) { … }
@2xl	42rem (672px)	@container (width >= 42rem) { … }
@3xl	48rem (768px)	@container (width >= 48rem) { … }
@4xl	56rem (896px)	@container (width >= 56rem) { … }
@5xl	64rem (1024px)	@container (width >= 64rem) { … }
@6xl	72rem (1152px)	@container (width >= 72rem) { … }
@7xl	80rem (1280px)	@container (width >= 80rem) { … }
```

- We are using tailwind 4, please make sure to not to use deprecated classes / code <https://tailwindcss.com/docs/upgrade-guide#removed-deprecated-utilities> and if you encounter an old code, upgrade it

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
