<?php
/**
 * PHP Character Map Generator
 *
 * Generates a character map contains selected characters in a TrueType font
 * to an image. Require PHP 5 or higher with GD extension enabled
 *
 * Example:
 * <code>
 * <?php
 *      # Generates a character map of the extended ASCII table of 'myfont.ttf'
 *      # with font size 14. The image will be output directly to the browser 
 *      # in PNG format (compression level = 9)
 *     charmap("myfont.ttf", 14, range(128, 255), "png|9");
 * ?>
 * </code>
 *
 * @author windylea
 * @copyright Copyright (c) 2013, WindyLea
 * @license http://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @param string $fontFile The path to the TrueType font you wish to use
 * @param float $fontSize The font size. Depending on your version of GD, 
        this should be specified as the pixel size (GD1) or point size (GD2)
 * @param array $chars List of the characters. Can be decimal notation, hex string,
        HTML entities or plain text
 * @param string $output The output mode, format and quality of the image
        - To set image quality, append $output with '|quality'
        - To output the image directly to browser, set the format name to $output
          such as 'png', 'jpg' or 'gif'
        - To save the image to a new file, set the file path to $output, for 
          example '/folder/charmap.png'
        - To save the image with the file name of the font file, use a 
          wildcard '*' like '/folder/*.jpeg'
 * @param array $colors The color indexes. May contains 4 elements: 'border', 
        'background', 'text' and 'index'
 * @param int $padding Space between the character and its cell's border
 * @return bool Returns TRUE on success, otherwise FALSE if the font file is not 
        readable or the image cannot be created
 * @version 20130410
 */
function charmap($fontFile, $fontSize = 12, $chars = array(), $output = "*.png|9", $colors = array(), $padding = 8)
{
    /*
     * Checks font file and get its file name
     */
    $fontFile = realpath($fontFile);
    if ($fontFile === false || !is_readable($fontFile))
    {
        return false;
    }

    $pathInfo = pathinfo($fontFile);
    $fontFileName = basename($fontFile, "." . $pathInfo['extension']);

    /*
     * Sets default variables
     */
    if (!is_int($fontSize))
    {
        $fontSize = 12;
    }

    if (!is_array($chars) || empty($chars))
    {
        $chars = range(0, 255);
    }

    $textColor = isset($colors["text"]) ? $colors["text"] : array(0, 0, 0);
    $borderColor = isset($colors["border"]) ? $colors["border"] : array(63, 63, 63);
    $backgroundColor = isset($colors["background"]) ? $colors["background"] : array(255, 255, 255);
    $indexColor = isset($colors["index"]) ? $colors["index"] : array(127, 127, 127);

    /*
     * Collects data from the characters, including Unicode code point and 
     * dimension
     */
    $data = array();
    $charWidths = array();
    $charHeights = array();

    $i = 0;
    foreach ($chars as $char)
    {
        $string = $char;

        /*
         * $char is in decimal notation
         */
        if (is_int($char))
        {
            $string = "&#" . $char . ";";
            $codePoint = dechex($char);
        /*
         * $char is a hex string
         */
        } elseif (is_string($char) && ctype_xdigit($char))
        {
            $string = "&#" . hexdec($char) . ";";
            $codePoint = $char;
        /*
         * $char is a HTML entity
         */
        } elseif (preg_match("/(&#x?)([0-9A-F]+);/i", $char, $match))
        {
            if ($match[1] == "&#")
            {
                $codePoint = dechex($match[2]);
            } else
            {
                $string = "&#" . hexdec($match[2]) . ";";
                $codePoint = $match[2];
            }
        } else
        /*
         * $char is plain text
         */
        {
            $codePoint = bin2hex(iconv("UTF-8", "UCS-2", $string));
        }

        $codePoint = ltrim(strtoupper($codePoint), "0");

        /*
         * Gets $char dimension
         */
        $bbox = imagettfbbox($fontSize, 0, $fontFile, $string);
        
        $data[$i] = array(
            "string" => $string, 
            "code_point" => $codePoint, 
            "ascent" => abs($bbox[7]), 
            "descent" => abs($bbox[1]), 
            "width" => abs($bbox[0]) + abs($bbox[2]), 
            "height" => abs($bbox[7]) + abs($bbox[1])
        );

        $charWidths[] = $data[$i]["width"];
        $charHeights[] = $data[$i]["height"];
        $i++;
    }

    /*
     * Calculates number of rows and columns in the image
     */
    $total = count($chars);
    $row = ceil(sqrt($total));
    if ($total % $row != 0)
    {
        $column = ceil($total / $row);
    } else
    {
        $column = $total / $row;
    }

    /*
     * Calculates the dimension for each character's cell in the table
     */
    $cellWidth = ceil(((max($charWidths) + min($charWidths)) / 2) * 2) + $padding * 2;
    $cellHeight = ceil(((max($charHeights) + min($charHeights)) / 2) * 2) + $padding * 2;

    /*
     * Creates a new true color image
     */
    $imageWidth = $cellWidth * $row + 1;
    $imageHeight = $cellHeight * $column + 1;
    $image = @imagecreatetruecolor($imageWidth, $imageHeight);
    if (!$image)
    {
        return false;
    }
    imageantialias($image, true);

    /*
     * Sets colors and fills the background
     */
    $colorText = imagecolorallocate($image, $textColor[0], $textColor[1], $textColor[2]);
    $colorBorder = imagecolorallocate($image, $borderColor[0], $borderColor[1], $borderColor[2]);
    $colorBackground = imagecolorallocate($image, $backgroundColor[0], $backgroundColor[1], $backgroundColor[2]);
    $colorIndex = imagecolorallocate($image, $indexColor[0], $indexColor[1], $indexColor[2]);
    imagefill($image, 0, 0, $colorBackground);
    #$indexSize = ($cellWidth < 65) ? 2 : 4;
    $indexSize = 1;
    for ($i = 1; $i <= 5; $i++)
    {
        $imageFontWidth = imagefontwidth($i) * 8;
        if ($cellWidth > $imageFontWidth)
        {
            $indexSize = $i;
        }
    }

    /*
     * Draws horizontal lines of the grid
     */
    for ($i = 0; $i <= $row; $i++)
    {
        imageline($image, $i * $cellWidth, 0, $i * $cellWidth, $imageHeight, $colorBorder);
    }

    /*
     * Draws vertical lines of the grid
     */
    for ($j = 0; $j <= $column; $j++)
    {
        imageline($image, 0, $j * $cellHeight, $imageWidth, $j * $cellHeight, $colorBorder);
    }

    /*
     * Writes the characters into their cells
     */
    $k = 0;
    for ($j = 0; $j < $column; $j++)
    {
        for ($i = 0; $i < $row; $i++)
        {
            if (!isset($data[$k]))
            {
                break;
            }
			
            /*
             * Write index code
             */
            imagestring($image, $indexSize, 
                $i * $cellWidth + 3, 
                $j * $cellHeight + 1, 
                $data[$k]["code_point"], $colorIndex);

            /*
             * Write the font's character
             */
            imagettftext($image, $fontSize, 0,
                $i * $cellWidth + ceil(($cellWidth - $data[$k]["width"]) / 2),
                $j * $cellHeight + ceil(($cellHeight - $data[$k]["height"]) / 2) + $data[$k]["ascent"],
                $colorText, $fontFile, $data[$k]["string"]);

            $k++;
        }
    }

    /*
     * Outputs the image to browser or save to a new file
     */
    $fileName = null;
    $quality = null;

    if (strpos($output, "|") !== false)
    {
        list($file, $quality) = explode("|", trim($output), 2);
    } else
    {
        $file = trim($output);
    }

    if (strpos($file, ".") !== false)
    {
        $pathInfo = pathinfo($file);
        $fileType = $pathInfo["extension"];
        $fileName = substr($pathInfo["basename"], 0, strrpos($pathInfo["basename"], "."));

        if ($fileName == "*")
        {
            $fileName = $pathInfo["dirname"] . "/" . $fontFileName;
        } else
        {
            $fileName = $file;
        }
    } else
    {
        $fileType = $file;
    }

    switch (strtolower($fileType))
    {
        case "gif":
            if (is_null($fileName))
            {
                header("Content-Disposition: inline;filename=" . $fontFileName . "." . $fileType);
                header("Content-Type: image/gif");
                imagegif($image, null);
            } else
            {
                imagegif($image, $fileName . "." . $fileType);
            }
            break;

        case "jpg":
        case "jpeg":
            $quality = is_null($quality) ? 85 : $quality;

            if (is_null($fileName))
            {
                header("Content-Disposition: inline;filename=" . $fontFileName . "." . $fileType);
                header("Content-Type: image/jpeg");
                imagejpeg($image, null, $quality);
            } else
            {
                imagejpeg($image, $fileName . "." . $fileType, $quality);
            }
            break;

        case "png":
        default:
            $quality = is_null($quality) ? 9 : $quality;

            if (is_null($fileName))
            {
                header("Content-Disposition: inline;filename=" . $fontFileName . ".png");
                header("Content-Type: image/png");
                imagepng($image, null, $quality, PNG_NO_FILTER);
            } else
            {
                imagepng($image, $fileName . ".png", $quality, PNG_NO_FILTER);
            }

            break;
    }

    imagedestroy($image);
    return true;
}
?>
