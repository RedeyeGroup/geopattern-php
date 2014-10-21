GeoPattern [![Build Status](https://travis-ci.org/redeyeventures/geopattern-php.png?branch=master)](https://travis-ci.org/redeyeventures/geopattern-php)
==========

This is a PHP port of [jasonlong/geo_pattern](https://github.com/jasonlong/geo_pattern).

Generate beautiful tiling SVG patterns from a string. The string is converted into a SHA and a color and pattern are determined based on the values in the hash. The color is determined by shifting the hue and saturation from a default (or passed in) base color. One of 16 patterns is used (or you can specify one) and the sizing of the pattern elements is also determined by the hash values.

You can use the generated pattern as the `background-image` for a container. Using the `base64` representation of the pattern still results in SVG rendering, so it looks great on retina displays.

See the [GitHub Guides](http://guides.github.com) site as an example of what this library can do. (GitHub Guides uses the original ruby version).

## Installation

Add this line to the require section of your composer.json file:

    "redeyeventures/geopattern": "1.1.*"

And then run:

    $ composer update

## Installation (without composer)

Download or clone the `src` directory from GitHub.

Rename the `src` folder to `GeoPattern` and put it somewhere your app can access it from.

Add this line to your code:

    require_once('path/to/folder/geopattern_loader.php');

You can then follow the usage instructions below.

## Usage

Make a new pattern:

    $geopattern = new \RedeyeVentures\GeoPattern\GeoPattern();
    $geopattern->setString('Mastering Markdown');

To specify a base background color (with a hue and saturation that adjusts depending on the string):

    $geopattern->setBaseColor('#ffcc00');

To use a specific background color (w/o any hue or saturation adjustments):

    $geopattern->setColor('#ffcc00');

To use a specific [pattern generator](#available-patterns):

    $geopattern->setGenerator('sine_waves');

Get the SVG string:

    $svg = $geopattern->toSVG();

Get the Base64 encoded string:

    $base64 = $geopattern->toBase64();

Get a data URI:

    $dataURI = $geopattern->toDataURI(); #data:image/svg+xml;base64,...

Get a data URL:

    $dataURL = $geopattern->toDataURL(); #url("data:image/svg+xml;base64,...")

You can use the data URL string to set the background:

    <div style="background-image: {$dataURL)"></div>

The `setString`, `setBaseColor`, `setGenerator` methods are chainable.
You can also pass an array to the GeoPattern constructor containing the `string`, `baseColor`, `color`, and/or `generator` values.

If the GeoPattern object is cast as a string, it will provide the SVG string.

## Contributing

1. Fork it ( http://github.com/redeyeventures/geopattern-php/fork )
2. Create your feature branch (`git checkout -b my-new-feature`)
3. Commit your changes (`git commit -am 'Add some feature'`)
4. Push to the branch (`git push origin my-new-feature`)
5. Create new Pull Request

## Original Project

See https://github.com/jasonlong/geo_pattern for more info and links to ports for other languages.

Based on jasonlong/geo_pattern @ ac27b5bb50a8d2061ff63254c915e9ca96a40480.
