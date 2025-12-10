<?php

namespace App\Support\Response;

trait PdfResponse
{
    public function pdf($content = '')
    {
        return response($content)
            ->header('Content-Type', 'application/pdf')
            ->header('Cache-Control', 'private, max-age=0, must-revalidate')
            ->header('Pragma', 'public');
    }
}
