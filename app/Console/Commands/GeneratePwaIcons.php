<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:generate-pwa-icons')]
#[Description('Generate PWA icons from favicon SVG')]
class GeneratePwaIcons extends Command
{
    public function handle(): int
    {
        $sizes = [192, 512];
        $svgPath = public_path('favicon.svg');

        if (! file_exists($svgPath)) {
            $this->error('favicon.svg not found in public directory.');

            return self::FAILURE;
        }

        foreach ($sizes as $size) {
            $image = imagecreatetruecolor($size, $size);
            $bg = imagecolorallocate($image, 27, 67, 50); // #1B4332
            imagefill($image, 0, 0, $bg);

            $white = imagecolorallocate($image, 255, 255, 255);
            $fontSize = (int) ($size * 0.45);
            $fontBBox = imagettfbbox($fontSize, 0, $this->getFont(), 'M');
            $textWidth = $fontBBox[2] - $fontBBox[0];
            $textHeight = $fontBBox[1] - $fontBBox[7];
            $x = (int) (($size - $textWidth) / 2);
            $y = (int) (($size + $textHeight) / 2);

            imagettftext($image, $fontSize, 0, $x, $y, $white, $this->getFont(), 'M');
            imagepng($image, public_path("pwa-{$size}x{$size}.png"));
            imagedestroy($image);

            $this->info("Created pwa-{$size}x{$size}.png");
        }

        // Maskable icon (with extra padding for safe zone)
        $size = 512;
        $image = imagecreatetruecolor($size, $size);
        $bg = imagecolorallocate($image, 27, 67, 50);
        imagefill($image, 0, 0, $bg);

        $white = imagecolorallocate($image, 255, 255, 255);
        $fontSize = (int) ($size * 0.35);
        $fontBBox = imagettfbbox($fontSize, 0, $this->getFont(), 'M');
        $textWidth = $fontBBox[2] - $fontBBox[0];
        $textHeight = $fontBBox[1] - $fontBBox[7];
        $x = (int) (($size - $textWidth) / 2);
        $y = (int) (($size + $textHeight) / 2);

        imagettftext($image, $fontSize, 0, $x, $y, $white, $this->getFont(), 'M');
        imagepng($image, public_path('pwa-maskable-512x512.png'));
        imagedestroy($image);

        $this->info('Created pwa-maskable-512x512.png');
        $this->newLine();
        $this->info('PWA icons generated successfully!');

        return self::SUCCESS;
    }

    private function getFont(): string
    {
        // Use a system font that's commonly available
        $fonts = [
            'C:/Windows/Fonts/arial.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/System/Library/Fonts/Helvetica.ttc',
        ];

        foreach ($fonts as $font) {
            if (file_exists($font)) {
                return $font;
            }
        }

        // Fallback: use GD built-in font (won't use imagettftext)
        return '';
    }
}
