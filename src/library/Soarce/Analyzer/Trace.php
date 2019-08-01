<?php

namespace Soarce\Analyzer;

use Soarce\Control\Service;

class Trace extends AbstractAnalyzer
{
    /**
     * @param  int   $application
     * @param  int   $usecase
     * @param  int   $request
     * @return array
     */
    public function getFiles($application = null, $usecase = null, $request = null): array
    {
        $sql = 'SELECT f.id, f.filename as `name`, COUNT(distinct c.id) as `calls`
            FROM `file`          f
            JOIN `function_call` c ON c.`file_id` = f.`id`
            JOIN `request`       r ON r.`id`      = f.`request_id` '     . ($usecase     !== null ? " and r.`usecase_id` = {$usecase} "     : '')
                                                                         . ($request     !== null ? " and r.`id`         = {$request} "     : '') . '
            JOIN `application`   a ON a.`id`      = r.`application_id` ' . ($application !== null ? " and a.`id`         = {$application} " : '') . '
            WHERE 1
            GROUP BY f.filename
            ORDER BY f.filename ASC';
        $ret = [];
        $result = $this->mysqli->query($sql);
        while ($row = $result->fetch_assoc()) {
            $ret[$row['id']] = $row;
        }
        return $ret;
    }

    /**
     * @param  int   $application
     * @param  int   $usecase
     * @param  int   $request
     * @param  int   $file
     * @return array
     */
    public function getFunctionCalls($application, $usecase, $request, $file): array
    {
        $sql = 'SELECT c.`class`, c.`function`, c.`type`
            FROM `function_call` c
            JOIN `file`          f ON c.`file_id` = f.`id` '             . ($file        !== null ? " and f.`id`         = {$file} "        : '') . '
            JOIN `request`       r ON r.`id`      = f.`request_id` '     . ($usecase     !== null ? " and r.`usecase_id` = {$usecase} "     : '')
                                                                         . ($request     !== null ? " and r.`id`         = {$request} "     : '') . '
            JOIN `application`   a ON a.`id`      = r.`application_id` ' . ($application !== null ? " and a.`id`         = {$application} " : '') . '
            WHERE 1
            GROUP BY c.class, c.function
            ORDER BY c.class ASC, c.function ASC';
        $result = $this->mysqli->query($sql);

        if ($result->num_rows > 0) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        return [];

    }







}
