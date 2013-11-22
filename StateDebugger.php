<?php
namespace MarijnKoesen\PhpDebugUtils;

/**
 * Debug helper function for logging and restoring requests.
 *
 * WARNING: Do not use in production environments.
 *
 * @author Marijn Koesen
 */

class StateDebugger
{
    protected $writableDirectory = '/tmp';
    protected $logPrefix = 'some-request-state-';

    /**
     * @param string $logPrefix The name of the requested page, used to prefix the logs
     * @param string $writableDirectory
     * @throws Exception
     */
    public function StateDebugger($logPrefix, $writableDirectory = '/tmp')
    {
        $this->logPrefix = $logPrefix;

        if (!is_dir($writableDirectory) || !is_writable($writableDirectory)) {
            throw new Exception('writableDirectory "' . $writableDirectory . '" needs to exist and be writable');
        }

        $this->writableDirectory = $writableDirectory;
    }

    /**
     * Enable the StateDebugger in the request. 
     * 
     * All requests will be logged to the writable dir and if the __STATE_DEBUGGER__ get 
     * parameter is set, this request log will be loaded and injected in the current
     * request ($_POST, $_GET etc)
     *
     * @param boolean $logCurrentRequest Switch whether to log the current request or not (and only list/load requests)
     */
    public function inject($logCurrentRequest=true)
    {
        $this->logCurrentRequest();

        if ( ! isset($_GET['__STATE_DEBUGGER__'])) {
            return;
        }

        if (empty($_GET['__STATE_DEBUGGER__']))  {
            return $this->listRequests();
        }

        $this->loadRequest($_GET['__STATE_DEBUGGER__']);
    }

    /**
     * Logs the URL and all request data (_POST, _GET, _SESSION, _COOKIE etc). to the writableDirectory
     *
     * @return string The path of the created log file
     */
    public function logCurrentRequest()
    {
        if ( ! $logCurrentRequest) 
            return false;

        // Write state file for request
        $dateWithMillisecs = date("Y-m-d_H-i-s", time()) . substr((string)microtime(), 1, 8);
        $stateFileName = tempnam($this->writableDirectory, $this->logPrefix . '-' . $dateWithMillisecs);

        $dump = json_encode(
            array(
                '_SERVER' => $_SERVER,
                '_GET' => $_GET,
                '_POST' => $_POST,
                '_REQUEST' => $_REQUEST,
                '_COOKIE' => $_COOKIE,
                '_SESSION' => isset($_SESSION) ? $_SESSION : array(),
            ),
            JSON_PRETTY_PRINT
        );

        file_put_contents($stateFileName, $dump);

        return $stateFileName;
    }

    /**
     * Load the data from the filename in the current request (_POST, _GET, _SESSION, _COOKIE etc).
     *
     * @param string $filename Filename containing the json data
     */
    public function loadRequest($filename)
    {
        $filename = preg_replace("/[^a-zA-Z-_\.0-9]/", "", $filename);

        list ($_SERVER, $_GET, $_POST, $_REQUEST, $_COOKIE, $_SESSION) =
            array_values(json_decode(file_get_contents($this->writableDirectory . '/' . $filename), true)); //DANGEROUS
    }

    /**
     * Displays a listing of logged requests.
     *
     * Clicking an item in that listing, re-creates that request.
     *
     * @param writableDirectory directory for saving the requests in files
     * @return void (call it solely for it's side-effects ;-))
     */
    public function listRequests()
    {
        $fileNames = scandir($this->writableDirectory);

        echo "<h1>Requests log</h1>";

        echo "<table>";
        foreach($fileNames as $filename) {
            $path = $this->writableDirectory . $filename;
            echo "<tr>";
            echo "<td><a href='?__STATE_DEBUGGER__=" . urlencode($filename) . "'>" . htmlentities($filename) . "</a></td>";
            echo "<td>" . date("Y-m-d H:i:s", filectime($path)) . "</td>"; 
            echo "</tr>";
        }
        echo "</table>";
    }
}
