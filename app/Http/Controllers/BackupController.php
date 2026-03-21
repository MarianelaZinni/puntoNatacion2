<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class BackupController extends Controller
{
    /**
     * Generate and download database backup
     */
    public function download()
    {
        try {
            $connection = config('database.default');
            $config = config("database.connections.{$connection}");
            
            $filename = 'backup_' . now()->format('Y-m-d_His') . '.sql';
            
            switch ($connection) {
                case 'mysql':
                    $backupContent = $this->getMysqlBackup($config);
                    break;
                default:
                    return back()->with('error', 'Tipo de base de datos no soportado para backup.');
            }
            
            if ($backupContent === false) {
                return back()->with('error', 'Error al generar el backup de la base de datos.');
            }
            
            return response($backupContent)
                ->header('Content-Type', 'application/sql')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
                
        } catch (\Exception $e) {
            return back()->with('error', 'Error al generar backup: ' . $e->getMessage());
        }
    }
    
    /**
     * Generate MySQL backup
     */
    private function getMysqlBackup($config)
    {
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? 3306;
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'] ?? '';
        
        // Build mysqldump command
        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s %s %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            $password ? '--password=' . escapeshellarg($password) : '',
            escapeshellarg($database)
        );
        
        $process = Process::fromShellCommandline($command);
        $process->setTimeout(300); // 5 minutes timeout
        
        try {
            $process->mustRun();
            return $process->getOutput();
        } catch (ProcessFailedException $e) {
            // Fallback: use PHP-based export for MySQL
            return $this->getMysqlBackupPHP($database);
        }
    }
    
    /**
     * PHP-based MySQL backup (fallback)
     */
    private function getMysqlBackupPHP($database)
    {
        $tables = DB::select('SHOW TABLES');
        $sql = "-- MySQL Database Backup\n";
        $sql .= "-- Generated: " . now()->toDateTimeString() . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
        
        foreach ($tables as $table) {
            $tableName = array_values((array)$table)[0];
            
            // Get CREATE TABLE statement
            $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
            $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
            $sql .= $createTable[0]->{'Create Table'} . ";\n\n";
            
            // Get table data
            $rows = DB::table($tableName)->get();
            
            if ($rows->count() > 0) {
                $sql .= "-- Dumping data for table `{$tableName}`\n\n";
                
                foreach ($rows as $row) {
                    $values = array_map(function($value) {
                        if (is_null($value)) {
                            return 'NULL';
                        }
                        return "'" . addslashes($value) . "'";
                    }, (array)$row);
                    
                    $columns = array_keys((array)$row);
                    $sql .= sprintf(
                        "INSERT INTO `%s` (`%s`) VALUES (%s);\n",
                        $tableName,
                        implode('`, `', $columns),
                        implode(', ', $values)
                    );
                }
                $sql .= "\n";
            }
        }
        
        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
        return $sql;
    }
}
