<?php
    use king\core\Error;

        if (isset($msg)) {
            echo htmlspecialchars($msg);
        }

        if ($file) {
            $sources = Error::debug($file, $line);
            foreach ($sources as $num => $row) {
                echo htmlspecialchars($row, ENT_NOQUOTES, 'utf-8');
            }

        }