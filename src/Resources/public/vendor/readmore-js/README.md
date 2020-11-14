# Readmore.js

A smooth, responsive jQuery plugin for collapsing and expanding long blocks of text with "Read more" and "Close" links.

The markup Readmore.js requires is so simple, you can probably use it with your existing HTML—there's no need for complicated sets of `div`'s or hardcoded classes, just call `.readmore()` on the element containing your block of text and Readmore.js takes care of the rest. Readmore.js plays well in a responsive environment, too.

Readmore.js is tested with—and supported on—all versions of jQuery greater than 1.9.1. All the "good" browsers are supported, as well as IE10+; IE8 & 9 _should_ work, but are not supported and the experience will not be ideal.


## Install

Install Readmore.js with npm:

```
$ npm install readmore-js
```

Then include it in your HTML:

```html
<script src="/node_modules/readmore-js/readmore.min.js"></script>
```

Or, using Webpack or Browserify:

```javascript
require('readmore-js');
```


## Use

```javascript
$('article').readmore();
```

It's that simple. You can change the speed of the animation, the height of the collapsed block, and the open and close elements.

```javascript
$('article').readmore({
  speed: 75,
  lessLink: '<a href="#">Read less</a>'
});
```

### The options:

* `speed: 100` in milliseconds
* `collapsedHeight: 200` in pixels
* `heightMargin: 16` in pixels, avoids collapsing blocks that are only slightly larger than `collapsedHeight`
* `moreLink: '<a href="#">Read more</a>'`
* `lessLink: '<a href="#">Close</a>'`
* `embedCSS: true` insert required CSS dynamically, set this to `false` if you include the necessary CSS in a stylesheet
* `blockCSS: 'display: block; width: 100%;'` sets the styling of the blocks, ignored if `embedCSS` is `false`
* `startOpen: false` do not immediately truncate, start in the fully opened position
* `beforeToggle: function() {}` called after a more or less link is clicked, but *before* the block is collapsed or expanded
* `afterToggle: function() {}` called *after* the block is collapsed or expanded
* `blockProcessed: function() {}` called once per block during initilization after Readmore.js has processed the block.

If the element has a `max-height` CSS property, Readmore.js will use that value rather than the value of the `collapsedHeight` option.

### The callbacks:

The `beforeToggle` and `afterToggle` callbacks both receive the same arguments: `trigger`, `element`, and `expanded`.

* `trigger`: the "Read more" or "Close" element that was clicked
* `element`: the block that is being collapsed or expanded
* `expanded`: Boolean; `true` means the block is expanded

The `blockProcessed` callback receives `element` and `collapsable`.

* `element`: the block that has just been processed
* `collapsable`: Boolean; `false` means the block was shorter than the specified minimum `collapsedHeight`--the block will not have a "Read more" link

#### Callback example:

Here's an example of how you could use the `afterToggle` callback to scroll back to the top of a block when the "Close" link is clicked.

```javascript
$('article').readmore({
  afterToggle: function(trigger, element, expanded) {
    if(! expanded) { // The "Close" link was clicked
      $('html, body').animate( { scrollTop: element.offset().top }, {duration: 100 } );
    }
  }
});
```

### Removing Readmore:

You can remove the Readmore.js functionality like so:

```javascript
$('article').readmore('destroy');
```

Or, you can be more surgical by specifying a particular element:

```javascript
$('article:first').readmore('destroy');
```

### Toggling blocks programmatically:

You can toggle a block from code:

```javascript
$('article:nth-of-type(3)').readmore('toggle');
```


## CSS:

Readmore.js is designed to use CSS for as much functionality as possible: collapsed height can be set in CSS with the `max-height` property; "collapsing" is achieved by setting `overflow: hidden` on the containing block and changing the `height` property; and, finally, the expanding/collapsing animation is done with CSS3 transitions.

By default, Readmore.js inserts the following CSS, in addition to some transition-related rules:

```css
selector + [data-readmore-toggle], selector[data-readmore] {
  display: block;
  width: 100%;
}
```

_`selector` would be the element you invoked `readmore()` on, e.g.: `$('selector').readmore()`_

You can override the base rules when you set up Readmore.js like so:

```javascript
$('article').readmore({blockCSS: 'display: inline-block; width: 50%;'});
```

If you want to include the necessary styling in your site's stylesheet, you can disable the dynamic embedding by setting `embedCSS` to `false`:

```javascript
$('article').readmore({embedCSS: false});
```

### Media queries and other CSS tricks:

If you wanted to set a `maxHeight` based on lines, you could do so in CSS with something like:

```css
body {
  font: 16px/1.5 sans-serif;
}

/* Show only 4 lines in smaller screens */
article {
  max-height: 6em; /* (4 * 1.5 = 6) */
}
```

Then, with a media query you could change the number of lines shown, like so:

```css
/* Show 8 lines on larger screens */
@media screen and (min-width: 640px) {
  article {
    max-height: 12em;
  }
}
```


## Contributing

Pull requests are always welcome, but not all suggested features will get merged. Feel free to contact me if you have an idea for a feature.

Pull requests should include the minified script and this readme and the demo HTML should be updated with descriptions of your new feature. 

You'll need NPM:

```
$ npm install
```

Which will install the necessary development dependencies. Then, to build the minified script:

```
$ npm run build
```

