# 2.0.0

## New features

- Install with Bower: `bower install readmore`
- Blocks can now be toggled programmatically: `$('article:nth-of-type(3)').readmore('toggle')`
- ARIA semantics describe expanded state and relationship between blocks and their toggles
- Blocks are now assigned an ID if they don't already have one
- Install development dependencies with NPM
- Gulp task to minifiy with UglifyJS 

## Improvements

- Height calculations on window resize are "debounced", resulting in more efficient rendering
- Height calculation in general has been improved
- The value of the `expanded` argument passed to the `beforeToggle` callback now correctly reflects the _pre-toggle_ state
- Multiple instances are now fully supported: e.g. `$('article').readmore({speed: 200})` and `$('fieldset').readmore({speed: 900})` will work on the same page
- Fully responsive, plugin now prefers max-heights set in CSS, even inside media queries

## Potentially breaking changes

- `maxHeight` option is now `collapsedHeight`
- `sectionCSS` option is now `blockCSS`
- `toggleSlider()` method is now just `toggle()`
- Animation is now performed with CSS3 transitions, rather than `jQuery.animate()`
- IE 8 and 9 are no longer supported, because those browsers hate kittens
- `init()` is now called within a `window.onload` event handler, which can briefly delay collapsing content
- `setBoxHeight()` is now a "private" method called `setBoxHeights()`
- `resizeBoxes()` is also now private
- Readmore.js now uses attribute selectors, rather than classes
    - The `.readmore-js-section` and `.readmore-js-toggle` classes are gone
    - The `expandedClass` and `collapsedClass` options are also gone
    - Every Readmore.js block needs an ID, if one is not already present, one will be generated 



