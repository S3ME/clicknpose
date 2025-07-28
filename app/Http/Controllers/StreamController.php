<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;

class StreamController extends Controller
{
    public function start()
    {
        $pidFile = storage_path('app/ffmpeg.pid');

        // Cek apakah ffmpeg sudah jalan
        if (file_exists($pidFile)) {
            $pid = (int) file_get_contents($pidFile);
            if ($pid && $this->isProcessRunning($pid)) {
                return response()->json(['status' => 'already running']);
            }
        }

        // Jalankan FFmpeg
        $command = [
            'ffmpeg',
            '-re',
            '-i', 'rtmp://localhost/live/stream',
            '-c:v', 'libx264',
            '-preset', 'veryfast',
            '-tune', 'zerolatency',
            '-c:a', 'aac',
            '-f', 'hls',
            '-hls_time', '2',
            '-hls_list_size', '5',
            '-hls_flags', 'delete_segments+omit_endlist',
            '-hls_segment_filename', public_path('hls/stream_%03d.ts'),
            public_path('hls/stream.m3u8'),
        ];

        $process = new Process($command);
        $process->setWorkingDirectory(base_path());
        $process->disableOutput();
        $process->start();

        // Simpan PID
        file_put_contents($pidFile, $process->getPid());

        return response()->json(['status' => 'started', 'pid' => $process->getPid()]);
    }

    private function isProcessRunning(int $pid): bool
    {
        $result = shell_exec("ps -p $pid");
        return str_contains($result, (string)$pid);
    }

    public function stop()
    {
        $pidFile = storage_path('app/ffmpeg.pid');

        if (!file_exists($pidFile)) {
            return response()->json(['status' => 'not running']);
        }

        $pid = (int) file_get_contents($pidFile);
        if (!$pid || !$this->isProcessRunning($pid)) {
            unlink($pidFile); // hapus file pid kalau proses tidak jalan
            return response()->json(['status' => 'already stopped']);
        }

        // Kill proses
        exec("kill $pid");

        // Hapus file pid
        unlink($pidFile);

        return response()->json(['status' => 'stopped']);
    }
}
