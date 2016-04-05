module.exports = {
    "root": true,
    "extends": "eslint:recommended",
    "env": {
        "browser": true,
        "es6": true
    },
    "globals": {
        "jQuery": true,
        "process": true,
        "__DEV__": true
    },
    "parserOptions": {
        "ecmaVersion": 7,
        "sourceType": "module",
        "ecmaFeatures": {
            "modules": true,
            "experimentalObjectRestSpread": true
        },
    },
    "rules": {
        "strict": [2, "function"],
        "indent": [2, 4, {"SwitchCase": 1}],
        "max-len": [2, {"code": 120, "tabWidth": 4, "ignoreUrls": true}],
        "eol-last": 2,
        // no space inside parentheses
        "space-in-parens": 2,
        "space-infix-ops": [2, {"int32Hint": false}],
        "space-unary-ops": 2,
        "no-multi-spaces": 2,
        "space-before-function-paren": [2, "always"],
        "semi-spacing": 2,
        // requires space after comment start
        "spaced-comment": 2,
        "quotes": [2, "single", "avoid-escape"],
        "quote-props": [2, "as-needed"],
        "linebreak-style": [2, "unix"],
        //
        "semi": [2, "always"],
        // require trailing commas in multiline object literals (better for diffs)
        "comma-dangle": [2, 'always-multiline'],
        // require let or const instead of var
        "no-var": 2,
        // suggest using of const declaration for variables that are never modified after declared
        "prefer-const": 2,
        // disallow modifying variables that are declared using const
        "no-const-assign": 2,
        "no-unused-vars": [1, {"args": "after-used"}],
        "no-else-return": 2,
        "eqeqeq": 2,
        "no-eq-null": 2,
        // no yoda conditions
        "yoda": 2,
        // require immediate function invocation to be wrapped in parentheses
        "wrap-iife": [2, "outside"],
        "func-names": [0],
        // disallow new for side-effects
        "no-new": 2,
        // Allow console.warn for deprecation notices
        "no-console": [2, {"allow": ["warn", "error"]}],
        "no-alert": 2,
        // Nice to have, but requires fixing legacy...
        //"require-jsdoc": 2,
        "valid-jsdoc": [2, {
            "requireReturnDescription": false,
            "requireParamDescription": false,
            "requireReturn": false
        }],
        "no-duplicate-imports": [2, {"includeExports": true}],
        // require method and property shorthand syntax for object literals
        // https://github.com/eslint/eslint/blob/master/docs/rules/object-shorthand.md
        "object-shorthand": [2, 'always'],
        // use rest parameters instead of arguments
        // http://eslint.org/docs/rules/prefer-rest-params
        "prefer-rest-params": 2,
        // suggest using the spread operator instead of .apply()
        'prefer-spread': 2,
        // http://eslint.org/docs/rules/arrow-body-style
        // Disabled because line-length rules
        'arrow-body-style': 0,
        // require parens in arrow function arguments
        'arrow-parens': [2, 'as-needed'],
        // require space before/after arrow function's arrow
        // https://github.com/eslint/eslint/blob/master/docs/rules/arrow-spacing.md
        "arrow-spacing": [2, {"before": true, "after": true}],
        // disallow symbol constructor
        // http://eslint.org/docs/rules/no-new-symbol
        "no-new-symbol": 2,
        "no-this-before-super": 2,
        // disallow unnecessary constructor
        // http://eslint.org/docs/rules/no-useless-constructor
        "no-useless-constructor": 2,
        // suggest using template literals instead of string concatenation
        // http://eslint.org/docs/rules/prefer-template
        "prefer-template": 2,
        "template-curly-spacing": [2, "never"]
    }
};
