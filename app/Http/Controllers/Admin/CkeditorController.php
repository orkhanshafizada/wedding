<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Settings;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CkeditorController extends Controller
{
    public function upload(Request $request)
    {
        /** CKEditor usually sends file in "upload" */
        /** @var UploadedFile|null $file */
        $file = $request->file('upload') ?: $request->file('file');

        // CKEditor Image dialog uses CKEditorFuncNum
        $funcNum = $request->input('CKEditorFuncNum');

        if (! $file) {
            return $this->errorResponse($funcNum, __('No file uploaded.'));
        }

        $allowed = Settings::get('file_manager', 'allowed_images', ['jpg','jpeg','png','gif','webp']);
        $maxKb   = $this->parseSizeToKilobytes((string) Settings::get('file_manager', 'max_image_size', '10MB'));

        $ext = strtolower($file->getClientOriginalExtension());

        if (! in_array($ext, $allowed, true)) {
            return $this->errorResponse($funcNum, __('Invalid image type.'));
        }

        // max rule in KB
        if (($file->getSize() / 1024) > $maxKb) {
            return $this->errorResponse($funcNum, __('Image is too large.'));
        }

        $dir  = 'ckeditor/' . now()->format('Y/m');
        $name = Str::uuid()->toString() . '.' . $ext;

        // store in "public" disk => storage/app/public/...
        $path = $file->storeAs($dir, $name, 'public');

        $url = asset('storage/' . $path);

        // If request is from CKEditor dialog (iframe)
        if ($funcNum) {
            $script = "<script>window.parent.CKEDITOR.tools.callFunction("
                . (int) $funcNum . ", "
                . "'" . e($url) . "', "
                . "''"
                . ");</script>";

            return response($script, 200)->header('Content-Type', 'text/html; charset=utf-8');
        }

        // If request is from uploadimage plugin (XHR)
        return response()->json([
            'uploaded'  => 1,
            'fileName'  => $file->getClientOriginalName(),
            'url'       => $url,
        ]);
    }

    private function errorResponse($funcNum, string $message)
    {
        if ($funcNum) {
            $script = "<script>window.parent.CKEDITOR.tools.callFunction("
                . (int) $funcNum . ", "
                . "''" . ", "
                . "'" . e($message) . "'"
                . ");</script>";

            return response($script, 200)->header('Content-Type', 'text/html; charset=utf-8');
        }

        return response()->json([
            'uploaded' => 0,
            'error' => ['message' => $message],
        ], 422);
    }

    private function parseSizeToKilobytes(string $val): int
    {
        if ($val === '') return 10240;

        if (preg_match('/^(\d+)\s*(kb|mb|gb)?$/i', $val, $m)) {
            $num  = (int) $m[1];
            $unit = strtolower($m[2] ?? 'mb');

            return match ($unit) {
                'kb' => $num,
                'gb' => $num * 1024 * 1024,
                default => $num * 1024,
            };
        }

        return (int) $val;
    }
}
