<?php namespace RedeyeVentures\GeoPattern;

use RedeyeVentures\GeoPattern\SVGElements\Polyline;
use RedeyeVentures\GeoPattern\SVGElements\Rectangle;
use RedeyeVentures\GeoPattern\SVGElements\Group;

class GeoPattern {

    protected $string;
    protected $baseColor;
    protected $generator;

    protected $hash;
    protected $svg;

    protected $patterns = [
        'octogons',
        'overlapping_circles',
        'plus_signs',
        'xes',
        'sine_waves',
        'hexagons',
        'overlapping_rings',
        'plaid',
        'triangles',
        'squares',
        'concentric_circles',
        'diamonds',
        'tessellation',
        'nested_squares',
        'mosaic_squares',
        'triangles_rotated',
        'chevrons',
    ];
    const FILL_COLOR_DARK = '#222';
    const FILL_COLOR_LIGHT = '#ddd';
    const STROKE_COLOR = '#000';
    const STROKE_OPACITY = '0.02';
    const OPACITY_MIN = '0.02';
    const OPACITY_MAX = '0.15';

    function __construct($options=array())
    {
        // Set string if provided. If not, set default.
        if (isset($options['string'])) {
            $this->setString($options['string']);
        } else {
            $this->setString(time());
        }

        // Set base color if provided. If not, set default.
        if (isset($options['baseColor'])) {
            $this->setBaseColor($options['baseColor']);
        } else {
            $this->setBaseColor('#933c3c');
        }

        // Set generator if provided. If not, leave null.
        if (isset($options['generator']))
            $this->setGenerator($options['generator']);
    }

    // Fluent Interfaces
    public function setString($string)
    {
        $this->string = $string;
        $this->hash = sha1($this->string);
        return $this;
    }

    public function setBaseColor($baseColor)
    {
        if(preg_match('/^#[a-f0-9]{6}$/i', $baseColor)) //hex color is valid
        {
            $this->baseColor = $baseColor;
            return $this;
        }
        throw new \InvalidArgumentException("$baseColor is not a valid hex color.");
    }

    public function setGenerator($generator)
    {
        $generator = strtolower($generator);
        if (in_array($generator, $this->patterns) || is_null($generator)) {
            $this->generator = $generator;
            return $this;
        }
        throw new \InvalidArgumentException("$generator is not a valid generator type.");
    }

    public function toSvgString()
    {
        $this->svg = new SVG();
        $this->generateBackground();
        $this->generatePattern();
        return (string) $this->svg;
    }

    public function toBase64String()
    {
        return base64_encode($this->toSvgString());
    }

    public function toDataURI()
    {
        return "data:image/svg+xml;base64,{$this->toBase64String()}";
    }

    public function toDataURL()
    {
        return "url(\"{$this->toDataURI()}\")";
    }

    public function __toString() {
        return $this->toSvgString();
    }

    protected function generateBackground()
    {
        $hueOffset = $this->map($this->hexVal(14, 3), 0, 4095, 0, 359);
        $satOffset = $this->hexVal(17, 1);
        $baseColor = $this->hexToHSL($this->baseColor);

        $baseColor['h'] = $baseColor['h'] - $hueOffset;


        if ($satOffset % 2 == 0)
            $baseColor['s'] = $baseColor['s'] + $satOffset/100;
        else
            $baseColor['s'] = $baseColor['s'] - $satOffset/100;

        $rgb = $this->hslToRGB($baseColor['h'], $baseColor['s'], $baseColor['l']);

        $this->svg->addRectangle(0, 0, "100%", "100%", ['fill' => "rgb({$rgb['r']}, {$rgb['g']}, {$rgb['b']})"]);
    }

    protected function generatePattern()
    {
        if (is_null($this->generator))
            $pattern = $this->patterns[$this->hexVal(20, 1)];
        else
            $pattern = $this->generator;

        $function = 'geo'.str_replace(' ', '', ucwords(str_replace('_', ' ', $pattern)));

        if (method_exists($this, $function))
            $this->$function();
        else
            $this->geoHexagons();
            //throw new \UnexpectedValueException("The generator function $function does not exist.");
    }

    protected function geoHexagons()
    {
        $scale = $this->hexVal(0, 1);
        $sideLength = $this->map($scale, 0, 15, 8, 60);
        $hexHeight = $sideLength * sqrt(3);
        $hexWidth = $sideLength * 2;
        $hex = $this->buildHexagonShape($sideLength);
        $this->svg->setWidth(($hexWidth * 3) + ($sideLength * 3))
            ->setHeight($hexHeight * 6);

        $i = 0;
        for ($y = 0; $y <= 5; $y++) {
            for ($x = 0; $x <= 5; $x++) {
                $val = $this->hexVal($i, 1);
                $dy = ($x % 2 == 0) ? ($y * $hexHeight) : ($y*$hexHeight + $hexHeight / 2);
                $opacity = $this->opacity($val);
                $fill = $this->fillColor($val);
                $styles = [
                    'stroke' => self::STROKE_COLOR,
                    'stroke-opacity' => self::STROKE_OPACITY,
                    'fill-opacity' => $opacity,
                    'fill' => $fill,
                ];

                $onePointFiveXSideLengthMinusHalfHexWidth = $x * $sideLength * 1.5 - $hexWidth / 2;
                $dyMinusHalfHexHeight = $dy - $hexHeight / 2;
                $this->svg->addPolyline($hex, array_merge($styles, ['transform' => "translate($onePointFiveXSideLengthMinusHalfHexWidth, $dyMinusHalfHexHeight)"]));

                // Add an extra one at top-right, for tiling.
                if ($x == 0) {
                    $onePointFiveSideLengthSixMinuxHalfHexWidth = 6 * $sideLength * 1.5 - $hexWidth / 2;
                    $this->svg->addPolyline($hex, array_merge($styles, ['transform' => "translate($onePointFiveSideLengthSixMinuxHalfHexWidth, $dyMinusHalfHexHeight)"]));
                }

                // Add an extra row at the end that matches the first row, for tiling.
                if ($y == 0) {
                    $dy2 = ($x % 2 == 0) ? (6 * $hexHeight) : (6 * $hexHeight + $hexHeight / 2);
                    $dy2MinuxHalfHexHeight = $dy2 - $hexHeight / 2;
                    $this->svg->addPolyline($hex, array_merge($styles, ['transform' => "translate($onePointFiveXSideLengthMinusHalfHexWidth, $dy2MinuxHalfHexHeight)"]));
                }

                // Add an extra one at bottom-right, for tiling.
                if ($x == 0 && $y == 0) {
                    $onePointFiveSideLengthSixMinuxHalfHexWidth = 6 * $sideLength * 1.5 - $hexWidth / 2;
                    $fiveHexHeightPlusHalfHexHeight = 5 * $hexHeight + $hexHeight / 2;
                    $this->svg->addPolyline($hex, array_merge($styles, ['transform' => "translate($onePointFiveSideLengthSixMinuxHalfHexWidth, $fiveHexHeightPlusHalfHexHeight)"]));
                }

                $i++;
            }
        }
    }

    protected function geoSineWaves()
    {
        $period = floor($this->map($this->hexVal(0, 1), 0, 15, 100, 400));
        $quarterPeriod = $period / 4;
        $xOffset = $period / 4 * 0.7;
        $amplitude = floor($this->map($this->hexVal(1, 1), 0, 15, 30, 100));
        $waveWidth = floor($this->map($this->hexVal(2, 1), 0, 15, 3, 30));
        $amplitudeString = number_format($amplitude);
        $halfPeriod = number_format($period / 2);
        $halfPeriodMinusXOffset = number_format($period / 2 - $xOffset);
        $periodMinusXOffset = number_format($period - $xOffset);
        $twoAmplitude = number_format(2 * $amplitude);
        $onePointFivePeriodMinusXOffset = number_format($period * 1.5 - $xOffset);
        $onePointFivePeriod = number_format($period * 1.5);
        $str = "M0 $amplitudeString C $xOffset 0, $halfPeriodMinusXOffset 0, $halfPeriod $amplitudeString S $periodMinusXOffset $twoAmplitude, $period $amplitudeString S $onePointFivePeriodMinusXOffset 0, $onePointFivePeriod, $amplitudeString";

        $this->svg->setWidth($period)
            ->setHeight($waveWidth*36);
        for ($i = 0; $i <= 35; $i++) {
            $val = $this->hexVal($i, 1);
            $opacity = $this->opacity($val);
            $fill = $this->fillColor($val);
            $styles = [
                'fill' => 'none',
                'stroke' => $fill,
                'style' => [
                    'opacity' => $opacity,
                    'stroke-width' => "{$waveWidth}px"
                ]
            ];

            $iWaveWidthMinusOnePointFiveAmplitude = $waveWidth * $i - $amplitude * 1.5;
            $iWaveWidthMinusOnePointFiveAmplitudePlusThirtySixWaveWidth = $waveWidth * $i - $amplitude * 1.5 + $waveWidth * 36;
            $this->svg->addPath($str, array_merge($styles, ['transform' => "translate(-$quarterPeriod, $iWaveWidthMinusOnePointFiveAmplitude)"]));
            $this->svg->addPath($str, array_merge($styles, ['transform' => "translate(-$quarterPeriod, $iWaveWidthMinusOnePointFiveAmplitudePlusThirtySixWaveWidth)"]));

        }
    }

    protected function geoChevrons()
    {
        $chevronWidth = $this->map($this->hexVal(0, 1), 0, 15, 30, 80);
        $chevronHeight = $this->map($this->hexVal(0, 1), 0, 15, 30, 80);
        $chevron = $this->buildChevronShape($chevronWidth, $chevronHeight);

        $this->svg->setWidth($chevronWidth*6)
            ->setHeight($chevronHeight*6*0.66);

        $i = 0;
        for ($y = 0; $y <= 5; $y++) {
            for ($x = 0; $x <= 5; $x++) {
                $val = $this->hexVal($i, 1);
                $opacity = $this->opacity($val);
                $fill = $this->fillColor($val);
                $styles = [
                    'stroke' => self::STROKE_COLOR,
                    'stroke-opacity' => self::STROKE_OPACITY,
                    'stroke-width' => '1',
                    'fill-opacity' => $opacity,
                    'fill' => $fill,
                ];

                $group = new Group();
                $group->addItem($chevron[0])
                    ->addItem($chevron[1]);

                $xChevronWidth = $x * $chevronWidth;
                $yPointSixSixChevronHeightMinusHalfChevronHeight = $y * $chevronHeight * 0.66 - $chevronHeight / 2;
                $this->svg->addGroup($group, array_merge($styles, ['transform' => "translate($xChevronWidth,$yPointSixSixChevronHeightMinusHalfChevronHeight)"]));
                // Add an extra row at the end that matches the first row, for tiling.
                if ($y == 0) {
                    $sixPointSixSixChevronHeightMinusHalfChevronHeight = 6 * $chevronHeight * 0.66 - $chevronHeight / 2;
                    $this->svg->addGroup($group, array_merge($styles, ['transform' => "translate($xChevronWidth,$sixPointSixSixChevronHeightMinusHalfChevronHeight)"]));
                }

                $i++;
            }
        }

    }



    // build* functions
    protected function buildChevronShape($width, $height)
    {
        $e = $height * 0.66;
        $halfWidth = $width / 2;
        $heightMinusE = $height - $e;
        return [
            new Polyline("0,0,$halfWidth,$heightMinusE,$halfWidth,$height,0,$e,0,0"),
            new Polyline("$halfWidth,$heightMinusE,$width,0,$width,$e,$halfWidth,$height,$halfWidth,$heightMinusE")
        ];
    }

    protected function buildOctogonShape($squareSize)
    {
        $s = $squareSize;
        $c = $s * 0.33;
        $sMinusC = $s - $c;
        return "$c,0,$sMinusC,0,$s,$c,$s,$sMinusC,$sMinusC,$s,$c,$s,0,$sMinusC,0,$c,$c,0";
    }

    protected function buildHexagonShape($sideLength)
    {
        $c = $sideLength;
        $a = $c/2;
        $b = sin(60 * M_PI / 180) * $c;
        $twoB = $b * 2;
        $twoC = $c * 2;
        $aPlusC = $a + $c;
        return "0,$b,$a,0,$aPlusC,0,$twoC,$b,$aPlusC,$twoB,$a,$twoB,0,$b";
    }

    protected function buildPlusShape($squareSize)
    {
        return [
            new Rectangle($squareSize, 0, $squareSize, $squareSize*3),
            new Rectangle(0, $squareSize, $squareSize*3, $squareSize),
        ];
    }

    protected function buildTriangleShape($sideLength, $height)
    {
        $halfWidth = $sideLength / 2;
        return "$halfWidth, 0, $sideLength, $height, 0, $height, $halfWidth, 0";
    }

    protected function buildRotatedTriangleShape($sideLength, $width)
    {
        $halfHeight = $sideLength / 2;
        return "0, 0, $width, $halfHeight, 0, $sideLength, 0, 0";
    }

    protected function buildRightTriangleShape($sideLength)
    {
        return "0, 0, $sideLength, $sideLength, 0, $sideLength, 0, 0";
    }

    protected function buildDiamondShape($width, $height)
    {
        $halfWidth = $width / 2;
        $halfHeight = $height / 2;
        return "$halfWidth, 0, $width, $halfHeight, $halfWidth, $height, 0, $halfHeight";
    }

    // draw* functions
    protected function drawInnerMosaicTile($x, $y, $triangleSize, $vals)
    {
        $triangle = $this->buildRightTriangleShape($triangleSize);
        $opacity = $this->opacity($vals[0]);
        $fill = $this->fillColor($vals[0]);
        $styles = [
            'stroke' => self::STROKE_COLOR,
            'stroke-opacity' => self::STROKE_OPACITY,
            'fill-opacity' => $opacity,
            'fill' => $fill,
        ];
        $xPlusTriangleSize = $x + $triangleSize;
        $yPlusTwoTriangleSize = $y + $triangleSize * 2;
        $this->svg->addPolyline($triangle, array_merge($styles, ['transform' => "translate($xPlusTriangleSize, $y) scale(-1, 1)"]))
            ->addPolyline($triangle, array_merge($styles, ['transform' => "translate($xPlusTriangleSize, $yPlusTwoTriangleSize) scale(1, -1)"]));

        $opacity = $this->opacity($vals[1]);
        $fill = $this->fillColor($vals[1]);
        $styles = [
            'stroke' => self::STROKE_COLOR,
            'stroke-opacity' => self::STROKE_OPACITY,
            'fill-opacity' => $opacity,
            'fill' => $fill,
        ];
        $xPlusTriangleSize = $x + $triangleSize;
        $yPlusTwoTriangleSize = $y + $triangleSize * 2;
        $this->svg->addPolyline($triangle, array_merge($styles, ['transform' => "translate($xPlusTriangleSize, $yPlusTwoTriangleSize) scale(-1, -1)"]))
            ->addPolyline($triangle, array_merge($styles, ['transform' => "translate($xPlusTriangleSize, $y) scale(1, 1)"]));

        return $this;
    }

    protected function drawOuterMosaicTile($x, $y, $triangleSize, $val)
    {
        $triangle = $this->buildRightTriangleShape($triangleSize);
        $opacity = $this->opacity($val);
        $fill = $this->fillColor($val);
        $styles = [
            'stroke' => self::STROKE_COLOR,
            'stroke-opacity' => self::STROKE_OPACITY,
            'fill-opacity' => $opacity,
            'fill' => $fill,
        ];

        $yPlusTriangleSize = $y + $triangleSize;
        $xPlusTwoTriangleSize = $x + $triangleSize * 2;
        $this->svg->addPolyline($triangle, array_merge($styles, ['transform' => "translate($x, $yPlusTriangleSize) scale(1, -1)"]))
            ->addPolyline($triangle, array_merge($styles, ['transform' => "translate($xPlusTwoTriangleSize, $yPlusTriangleSize) scale(-1, -1)"]))
            ->addPolyline($triangle, array_merge($styles, ['transform' => "translate($x, $yPlusTriangleSize) scale(1, 1)"]))
            ->addPolyline($triangle, array_merge($styles, ['transform' => "translate($xPlusTwoTriangleSize, $yPlusTriangleSize) scale(-1, 1)"]));
    }

    // Utility Functions

    protected function fillColor($val)
    {
        return ($val % 2 == 0) ? self::FILL_COLOR_LIGHT : self::FILL_COLOR_DARK;
    }

    protected function opacity($val)
    {
        return $this->map($val, 0, 15, self::OPACITY_MIN, self::OPACITY_MAX);
    }

    protected function hexVal($index, $len)
    {
        return hexdec(substr($this->hash, $index, $len));
    }

    // PHP implementation of Processing's map function
    // http://processing.org/reference/map_.html
    protected function map($value, $vMin, $vMax, $dMin, $dMax)
    {
        $vValue = floatval($value);
        $vRange = $vMax - $vMin;
        $dRange = $dMax - $dMin;
        return ($vValue - $vMin) * $dRange / $vRange + $dMin;
    }

    // Color Functions
    protected function hexToHSL($color)
    {
        $color = trim($color, '#');
        $R = hexdec($color[0].$color[1]);
        $G = hexdec($color[2].$color[3]);
        $B = hexdec($color[4].$color[5]);

        $HSL = array();

        $var_R = ($R / 255);
        $var_G = ($G / 255);
        $var_B = ($B / 255);

        $var_Min = min($var_R, $var_G, $var_B);
        $var_Max = max($var_R, $var_G, $var_B);
        $del_Max = $var_Max - $var_Min;

        $L = ($var_Max + $var_Min)/2;

        if ($del_Max == 0)
        {
            $H = 0;
            $S = 0;
        }
        else
        {
            if ( $L < 0.5 ) $S = $del_Max / ( $var_Max + $var_Min );
            else            $S = $del_Max / ( 2 - $var_Max - $var_Min );

            $del_R = ( ( ( $var_Max - $var_R ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
            $del_G = ( ( ( $var_Max - $var_G ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
            $del_B = ( ( ( $var_Max - $var_B ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;

            if      ($var_R == $var_Max) $H = $del_B - $del_G;
            else if ($var_G == $var_Max) $H = ( 1 / 3 ) + $del_R - $del_B;
            else if ($var_B == $var_Max) $H = ( 2 / 3 ) + $del_G - $del_R;

            if ($H<0) $H++;
            if ($H>1) $H--;
        }

        $HSL['h'] = ($H*360);
        $HSL['s'] = $S;
        $HSL['l'] = $L;

        return $HSL;
    }

    protected function hexToRGB($hex) {
        $hex = str_replace("#", "", $hex);
        if(strlen($hex) == 3) {
            $r = hexdec(substr($hex,0,1).substr($hex,0,1));
            $g = hexdec(substr($hex,1,1).substr($hex,1,1));
            $b = hexdec(substr($hex,2,1).substr($hex,2,1));
        } else {
            $r = hexdec(substr($hex,0,2));
            $g = hexdec(substr($hex,2,2));
            $b = hexdec(substr($hex,4,2));
        }
        return ['r' => $r, 'g' => $g, 'b' => $b];
    }

    protected function rgbToHSL($r, $g, $b) {
        $r /= 255;
        $g /= 255;
        $b /= 255;
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;
        if ($max == $min) {
            $h = $s = 0;
        } else {
            $d = $max - $min;
            $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);
            switch ($max) {
                case $r:
                    $h = ($g - $b) / $d + ($g < $b ? 6 : 0);
                    break;
                case $g:
                    $h = ($b - $r) / $d + 2;
                    break;
                case $b:
                    $h = ($r - $g) / $d + 4;
                    break;
            }
            $h /= 6;
        }
        $h = floor($h * 360);
        $s = floor($s * 100);
        $l = floor($l * 100);
        return ['h' => $h, 's' => $s, 'l' => $l];
    }

    protected function hslToRGB ($h, $s, $l) {
        $h += 360;
        $c = ( 1 - abs( 2 * $l - 1 ) ) * $s;
        $x = $c * ( 1 - abs( fmod( ( $h / 60 ), 2 ) - 1 ) );
        $m = $l - ( $c / 2 );

        if ( $h < 60 ) {
            $r = $c;
            $g = $x;
            $b = 0;
        } else if ( $h < 120 ) {
            $r = $x;
            $g = $c;
            $b = 0;
        } else if ( $h < 180 ) {
            $r = 0;
            $g = $c;
            $b = $x;
        } else if ( $h < 240 ) {
            $r = 0;
            $g = $x;
            $b = $c;
        } else if ( $h < 300 ) {
            $r = $x;
            $g = 0;
            $b = $c;
        } else {
            $r = $c;
            $g = 0;
            $b = $x;
        }

        $r = ( $r + $m ) * 255;
        $g = ( $g + $m ) * 255;
        $b = ( $b + $m  ) * 255;

        return array( 'r' => floor( $r ), 'g' => floor( $g ), 'b' => floor( $b ) );

    }

    //NOT USED
    protected function rgbToHex($r, $g, $b) {
        $hex = "#";
        $hex .= str_pad(dechex($r), 2, "0", STR_PAD_LEFT);
        $hex .= str_pad(dechex($g), 2, "0", STR_PAD_LEFT);
        $hex .= str_pad(dechex($b), 2, "0", STR_PAD_LEFT);
        return $hex;
    }


}