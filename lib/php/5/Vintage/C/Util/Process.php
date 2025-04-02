<?php

  namespace Vintage\C\Util {

    /*** Process */
    final class Process {

      /*** Start */
      public static function start(array $a):array {

        $x = ['status' => false];
        $r = [];

        // Check (Arguments)
        $r = self::check_a((array_merge($a, ['method' => 'start'])));

        if (!$r['status']) {
          $x['message'] = 'Arguments wrong.';
          $x['line']    = __LINE__;
          goto NG;
        }

        $a = $r['a'];
        $r = [];
        ///

        // $fp_free, $sequence_free, $count
        $fp_free       = null;
        $sequence_free = null;

        $count = [
          'dead'    => 0,
          'free'    => $a['processes_max'],
          'killed'  => 0,
          'running' => 0
        ];

        for ($i = 1; $i <= $a['processes_max']; $i++) {

          $fp = self::fp(array_merge($a, ['sequence' => $i]));

          if (!$fp) {
            $x['message'] = 'Failed to get file path.';
            $x['line']    = __LINE__;
            goto NG;
          }
          else if (file_exists($fp)) {

            $contents = file_get_contents($fp);
            $process  = unserialize($contents);

            $command        = 'ps -p ' . $process['id'];
            $command_output = null;
            $command_status = null;

            exec($command, $command_output, $command_status);

            if ($command_status === 0) {
              $count['free']--;
              $count['running']++;
            }
            else if (!unlink($fp)) {
              $x['message'] = 'Failed to unlink dead file.';
              $x['line']    = __LINE__;
              goto NG;
            }
            else {

              $fp_free       = $fp_free       ?? $fp;
              $sequence_free = $sequence_free ?? $i;

              $count['dead']++;
            }
          }
          else {
            $fp_free       = $fp_free       ?? $fp;
            $sequence_free = $sequence_free ?? $i;
          }
        }

        if (!$count['free']) {
          $x['message'] = 'No free processes.';
          $x['line']    = __LINE__;
          goto NG;
        }
        ///

        // Start
        $pid = getmypid();

        if (!is_numeric($pid)) {
          $x['message'] = 'Failed to get PID.';
          $x['line']    = __LINE__;
          goto NG;
        }

        $process = [
          'id'         => $pid,
          'project'    => $a['project'],
          'name'       => $a['name'],
          'number'     => $a['number'],
          'sequence'   => $sequence_free,
          'file'       => $fp_free,
          'started_at' => date('Y-m-d H:i:s')
        ];

        $contents = serialize($process);

        if (!file_put_contents($fp_free, $contents)) {
          $x['message'] = 'Failed to start.';
          $x['line']    = __LINE__;
          goto NG;
        }

        $count['free']--;
        $count['running']++;
        ///

        OK :

        $x['status']  = true;
        $x['message'] = $x['message'] ?? 'ok';
        $x['line']    = $x['line']    ?? __LINE__;

        $x['id']      = $pid; // Backward Compatibility
        $x['count']   = $count;
        $x['process'] = $process;

        return $x;

        NG :

        $x['trace']   = $r['trace'] ?? [];
        $x['trace'][] = [
          'message' => $x['message'],
          'line'    => $x['line'],
          'class'   => __CLASS__,
          'method'  => __METHOD__
        ];

        return $x;
      }

      /*** Finish */
      public static function finish(array $a):array {

        $x = ['status' => false];
        $r = [];

        // Check (Arguments)
        $r = self::check_a(array_merge($a, [
          'method' => 'finish'
        ]));

        if (!$r['status']) {
          $x['message'] = 'Arguments wrong.';
          $x['line']    = __LINE__;
          goto NG;
        }

        $a = $r['a'];
        $r = [];
        ///

        // Check (File)
        $fp = self::fp($a);

        if (!$fp) {
          $x['message'] = 'Failed to get file path.';
          $x['line']    = __LINE__;
          goto NG;
        }
        else if (!file_exists($fp)) {
          $x['message'] = 'File not found.';
          $x['line']    = __LINE__;
          goto NG;
        }

        $contents = file_get_contents($fp);
        $process  = unserialize($contents);

        if ($a['id'] != $process['id']) {
          $x['message'] = 'IDs not matched.';
          $x['line']    = __LINE__;
          goto NG;
        }
        ///

        // Finish
        if (!unlink($fp)) {
          $m            = 'Failed to unlink %s.';
          $x['message'] = sprintf($m, $fp);
          $x['line']    = __LINE__;
          goto NG;
        }

        OK :

        $x['status']  = true;
        $x['message'] = $x['message'] ?? 'ok';
        $x['line']    = $x['line']    ?? __LINE__;

        $process['finish_at'] = date('Y-m-d H:i:s');

        $x['process'] = $process;

        return $x;

        NG :

        $x['trace']   = $r['trace'] ?? [];
        $x['trace'][] = [
          'message' => $x['message'],
          'line'    => $x['line'],
          'class'   => __CLASS__,
          'method'  => __METHOD__
        ];

        return $x;
      }

      /*** File Path */
      private static function fp(array $a) {

        $fp = null;

        $dp_root = $_SERVER['VTG_ROOT'] ?? '';
        $dp      = $dp_root . '/tmp';

        if (!is_dir($dp)) {
          if (!mkdir($dp, 0755, true)) {
            goto FIN;
          }
        }

        $fp = sprintf(
          '%s/%s.%s.%d.%d.process',
          $dp,
          $a['project'],
          $a['name'],
          $a['number'],
          $a['sequence']
        );

        FIN : return $fp;
      }

      /*** Check (Arguments) */
      private static function check_a(array $a) {

        $x = ['status' => false];
        $r = [];

        // Common
        {
          $a['method']  = $a['method']  ?? null;
          $a['project'] = $a['project'] ?? 'vintage';
          $a['name']    = $a['name']    ?? null;
          $a['number']  = $a['number']  ?? 1;

          if (!strlen($a['project'])) {
            $x['message'] = '$a["project"] wrong.';
            $x['line']    = __LINE__;
            goto NG;
          }
          else if (!strlen($a['name'])) {
            $x['message'] = '$a["name"] wrong.';
            $x['line']    = __LINE__;
            goto NG;
          }
          else if (!preg_match('/^[1-9][0-9]*$/', $a['number'])) {
            $x['message'] = '$a["number"] wrong.';
            $x['line']    = __LINE__;
            goto NG;
          }

          $a['number'] = (int) $a['number'];
        }

        // Start
        if ($a['method'] == 'start') {

          $a['processes_max'] = $a['processes_max'] ?? 1;

          if (!preg_match('/^[1-9][0-9]*$/', $a['processes_max'])) {
            $x['message'] = '$a["processes_max"] wrong.';
            $x['line']    = __LINE__;
            goto NG;
          }

          $a['processes_max'] = (int) $a['processes_max'];
        }
        // Finish
        else if ($a['method'] == 'finish') {

          $a['id']       = $a['id']       ?? null;
          $a['sequence'] = $a['sequence'] ?? null;

          if (!$a['id']) {
            $x['message'] = '$a["id"] wrong.';
            $x['line']    = __LINE__;
            goto NG;
          }
          else if (!preg_match('/^[1-9][0-9]*$/', $a['id'])) {
            $x['message'] = '$a["id"] wrong.';
            $x['line']    = __LINE__;
            goto NG;
          }
          else if (!$a['sequence']) {
            $x['message'] = '$a["sequence"] wrong.';
            $x['line']    = __LINE__;
            goto NG;
          }
          else if (!preg_match('/^[1-9][0-9]*$/', $a['sequence'])) {
            $x['message'] = '$a["sequence"] wrong.';
            $x['line']    = __LINE__;
            goto NG;
          }

          $a['id']       = (int) $a['id'];
          $a['sequence'] = (int) $a['sequence'];
        }
        else {
          $x['message'] = '$a["method"] wrong.';
          $x['line']    = __LINE__;
          goto NG;
        }

        OK :

        $x['status']  = true;
        $x['message'] = $x['message'] ?? 'ok';
        $x['line']    = $x['line']    ?? __LINE__;

        $x['a'] = $a;

        return $x;

        NG :

        $x['trace']   = $r['trace'] ?? [];
        $x['trace'][] = [
          'message' => $x['message'],
          'line'    => $x['line'],
          'class'   => __CLASS__,
          'method'  => __METHOD__
        ];

        return $x;
      }
    }
  }

?>
