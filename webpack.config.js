var path = require('path');

var webpack = require('webpack');
var autoprefixer = require('autoprefixer');
var postcssImport = require('postcss-import');
var postcssInlineSvg = require('postcss-inline-svg');
var ExtractTextPlugin = require('extract-text-webpack-plugin');


var SRC_DIR = path.resolve(__dirname, 'Resources/assets');
var BUILD_DIR = path.resolve(__dirname, 'Resources/public');


module.exports = function (options) {
    var dev = options.dev;

    var extractCss = new ExtractTextPlugin('[name].css');

    var plugins = [
        new webpack.NoErrorsPlugin(),
        new webpack.DefinePlugin({
            'process.env.NODE_ENV': JSON.stringify(dev ? 'development' : 'production'),
            __DEV__: dev,
        }),
        new webpack.LoaderOptionsPlugin({
            test: /\.css$/,
            minimize: !dev,
            debug: dev
        }),
        extractCss
    ].concat(dev ? [] : [
        new webpack.optimize.UglifyJsPlugin()
    ]);

    return {
        debug: dev,
        devtool: dev ? 'source-map' : '',
        entry: {
            main: [
                path.join(SRC_DIR, 'css/main.css'),
                path.join(SRC_DIR, 'js/main.js')
            ],
        },
        output: {
            path: BUILD_DIR,
            filename: '[name].js'
        },
        externals: {
            jquery: 'jQuery'
        },
        plugins: plugins,
        resolve: {
            extensions: ['.js', '.json'],
            enforceExtension: false,
            modules: [
                path.join(SRC_DIR, 'js'),
                'node_modules',
            ],
        },
        module: {
            loaders: [
                {
                    test: /\.js$/,
                    exclude: /node_modules/,
                    loaders: [
                        {
                            loader: 'babel-loader',
                            query: {
                                presets: ['es2015', 'stage-2'],
                                plugins: [
                                    // 'external-helpers',
                                    ['transform-runtime', {
                                        //polyfill: true,
                                        regenerator: false
                                    }]
                                ]
                            }
                        }
                    ]
                },
                {
                    test: /\.html$/,
                    loaders: ['raw-loader']
                },
                {
                    test: /\.css$/,
                    loaders: extractCss.extract([
                        'css' + (dev ? '?sourceMap' : ''),
                        'postcss'
                    ]),
                }
            ]
        },
        postcss: function (webpack) {
            return {
                defaults: [
                    postcssImport({
                        addDependencyTo: webpack
                    }),
                    postcssInlineSvg,
                    autoprefixer({
                        // copied from bootstrap 3 config
                        browsers: [
                            "Android 2.3",
                            "Android >= 4",
                            "Chrome >= 20",
                            "Firefox >= 24",
                            "Explorer >= 8",
                            "iOS >= 6",
                            "Opera >= 12",
                            "Safari >= 6"
                        ]
                    })
                ]
            }
        }
    };
};
