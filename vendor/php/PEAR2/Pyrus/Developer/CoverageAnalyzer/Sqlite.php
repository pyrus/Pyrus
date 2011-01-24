<?php
namespace PEAR2\Pyrus\Developer\CoverageAnalyzer;
class Sqlite
{
    protected $db;
    protected $totallines = 0;
    protected $coveredlines = 0;
    protected $deadlines = 0;
    protected $pathCovered = array();
    protected $pathTotal = array();
    protected $pathDead = array();
    public $codepath;
    public $testpath;

    private $statement;
    private $lines = array();
    private $files = array();
    private $deleted = array();

    const COVERAGE_COVERED      = 1;
    const COVERAGE_NOT_EXECUTED = 0;
    const COVERAGE_NOT_COVERED  = -1;
    const COVERAGE_DEAD         = -2;

    function __construct($path = ':memory:', $codepath = null, $testpath = null, $codefiles = array())
    {
        $this->files = $codefiles;
        $this->db = new \Sqlite3($path);
        $this->db->exec('PRAGMA temp_store=2');
        $this->db->exec('PRAGMA count_changes=OFF');

        $version = '5.3.0';
        $sql = 'SELECT version FROM analyzerversion';
        if (@$this->db->querySingle($sql) == $version) {
            $this->codepath = $this->db->querySingle('SELECT codepath FROM paths');
            $this->testpath = $this->db->querySingle('SELECT testpath FROM paths');
            return;
        }

        // restart the database
        echo "Upgrading database to version $version";
        if (!$codepath || !$testpath) {
            throw new Exception('Both codepath and testpath must be set in ' .
                                'order to initialize a coverage database');
        }

        $this->codepath = $codepath;
        $this->testpath = $testpath;
        $this->db->exec('DROP TABLE IF EXISTS coverage;');
        echo ".";
        $this->db->exec('DROP TABLE IF EXISTS coverage_nonsource;');
        echo ".";
        $this->db->exec('DROP TABLE IF EXISTS not_covered;');
        echo ".";
        $this->db->exec('DROP TABLE IF EXISTS files;');
        echo ".";
        $this->db->exec('DROP TABLE IF EXISTS tests;');
        echo ".";
        $this->db->exec('DROP TABLE IF EXISTS paths;');
        echo ".";
        $this->db->exec('DROP TABLE IF EXISTS coverage_per_file;');
        echo ".";
        $this->db->exec('DROP TABLE IF EXISTS line_info;');
        echo ".";
        $this->db->exec('DROP TABLE IF EXISTS all_lines;');
        echo ".";
        $this->db->exec('DROP TABLE IF EXISTS xdebugs;');
        echo ".";
        $this->db->exec('DROP TABLE IF EXISTS analyzerversion;');

        echo ".";
        $this->db->exec('BEGIN');

        $sql = '
            CREATE TABLE coverage (
              files_id integer NOT NULL,
              tests_id integer NOT NULL,
              linenumber INTEGER NOT NULL,
              state INTEGER NOT NULL,
              PRIMARY KEY (files_id, linenumber, tests_id)
            );

            CREATE INDEX idx_coveragestats  ON coverage (files_id, tests_id, state);
            CREATE INDEX idx_coveragestats2 ON coverage (files_id, linenumber, tests_id, state);
            CREATE INDEX idx_coveragestats3 ON coverage (files_id, tests_id);

            CREATE TABLE all_lines (
              files_id integer NOT NULL,
              linenumber INTEGER NOT NULL,
              state INTEGER NOT NULL,
              PRIMARY KEY (files_id, linenumber, state)
            );

             CREATE INDEX idx_all_lines_stats ON all_lines (files_id, linenumber);

            CREATE TABLE line_info (
              files_id integer NOT NULL,
              covered INTEGER NOT NULL,
              dead  INTEGER NOT NULL,
              total INTEGER NOT NULL,
              PRIMARY KEY (files_id)
            );
          ';
        $this->exec($sql);

        echo ".";
        $sql = '
          CREATE TABLE coverage_nonsource (
            files_id integer NOT NULL,
            tests_id integer NOT NULL,
            PRIMARY KEY (files_id, tests_id)
          );
          ';
        $this->exec($sql);

        echo ".";
        $sql = '
          CREATE TABLE files (
            id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
            path TEXT(500) NOT NULL,
            hash TEXT(32) NOT NULL,
            issource BOOL NOT NULL,
            UNIQUE (path)
          );
          CREATE INDEX files_issource on files (issource);
          ';
        $this->exec($sql);

        echo ".";
        $sql = '
          CREATE TABLE xdebugs (
            path TEXT(500) NOT NULL,
            hash TEXT(32) NOT NULL,
            PRIMARY KEY (path)
          );';
        $this->exec($sql);

        echo ".";
        $sql = '
          CREATE TABLE tests (
            id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
            testpath TEXT(500) NOT NULL,
            hash TEXT(32) NOT NULL,
            UNIQUE (testpath)
          );';
        $this->exec($sql);

        echo ".";
        $sql = '
          CREATE TABLE analyzerversion (
            version TEXT(5) NOT NULL
          );

          INSERT INTO analyzerversion VALUES("' . $version . '");

          CREATE TABLE paths (
            codepath TEXT NOT NULL,
            testpath TEXT NOT NULL
          );';
        $this->exec($sql);

        echo ".";
        $sql = '
          INSERT INTO paths VALUES(
            "' . $this->db->escapeString($codepath) . '",
            "' . $this->db->escapeString($testpath). '");';
        $this->exec($sql);
        $this->db->exec('COMMIT');
        echo "done\n";
    }

    public function exec($sql)
    {
        $worked = $this->db->exec($sql);
        if (!$worked) {
            @$this->db->exec('ROLLBACK');
            $error = $this->db->lastErrorMsg();
            throw new Exception('Unable to create Code Coverage SQLite3 database: ' . $error);
        }
    }

    function retrieveLineLinks($file, $id = null)
    {
        if ($id === null) {
            $id = $this->getFileId($file);
        }

        $sql = 'SELECT t.testpath, c.linenumber
            FROM
                coverage c, tests t
            WHERE
                c.files_id = ' . $id . ' AND t.id = c.tests_id';
        $result = $this->db->query($sql);
        if (!$result) {
            $error = $this->db->lastErrorMsg();
            throw new Exception('Cannot retrieve line links for ' . $file .
                                ' line #' . $line .  ': ' . $error);
        }

        $ret = array();
        while ($res = $result->fetchArray(SQLITE3_ASSOC)) {
            $ret[$res['linenumber']][] = $res['testpath'];
        }
        return $ret;
    }

    function retrieveTestPaths()
    {
        $sql = 'SELECT testpath from tests ORDER BY testpath';
        $result = $this->db->query($sql);
        if (!$result) {
            $error = $this->db->lastErrorMsg();
            throw new Exception('Cannot retrieve test paths :' . $error);
        }
        $ret = array();
        while ($res = $result->fetchArray(SQLITE3_NUM)) {
            $ret[] = $res[0];
        }
        return $ret;
    }

    function retrievePathsForTest($test, $all = 0)
    {
        $id = $this->getTestId($test);
        $ret = array();
        if ($all) {
            $sql = '
                SELECT DISTINCT path
                FROM coverage_nonsource c, files
                WHERE c.tests_id = ' . $id . '
                    AND files.id = c.files_id
                GROUP BY c.files_id
                ORDER BY path';
            $result = $this->db->query($sql);
            if (!$result) {
                $error = $this->db->lastErrorMsg();
                throw new Exception('Cannot retrieve file paths for test ' . $test . ':' . $error);
            }

            while ($res = $result->fetchArray(SQLITE3_NUM)) {
                $ret[] = $res[0];
            }
        }

        $sql = '
            SELECT DISTINCT path
            FROM coverage c, files
            WHERE
                c.tests_id = ' . $id . '
              AND
                files.id = c.files_id
            GROUP BY c.files_id
            ORDER BY path';
        $result = $this->db->query($sql);
        if (!$result) {
            $error = $this->db->lastErrorMsg();
            throw new Exception('Cannot retrieve file paths for test ' . $test . ':' . $error);
        }

        while ($res = $result->fetchArray(SQLITE3_NUM)) {
            $ret[] = $res[0];
        }

        return $ret;
    }

    function retrievePaths($all = 0)
    {
        if ($all) {
            $sql = 'SELECT path from files ORDER BY path';
        } else {
            $sql = 'SELECT path from files WHERE issource = 1 ORDER BY path';
        }

        $result = $this->db->query($sql);
        if (!$result) {
            $error = $this->db->lastErrorMsg();
            throw new Exception('Cannot retrieve file paths :' . $error);
        }

        $ret = array();
        while ($res = $result->fetchArray(SQLITE3_NUM)) {
            $ret[] = $res[0];
        }

        return $ret;
    }

    function coveragePercentage($sourcefile, $testfile = null)
    {
        if ($testfile) {
            $coverage = $this->retrievePathCoverageByTest($sourcefile, $testfile);
        } else {
            $coverage = $this->retrievePathCoverage($sourcefile);
        }

        if ($coverage[1]) {
            return round(($coverage[0] / $coverage[1]) * 100, 1);
        }

        return 0;
    }

    function retrieveProjectCoverage($path = null)
    {
        if ($this->totallines) {
            return array($this->coveredlines, $this->totallines, $this->deadlines);
        }

        $sql = '
            SELECT covered, total, dead, path
            FROM line_info, files
            WHERE files.id = line_info.files_id';
        if ($path !== null) {
            $sql .= ' AND files.path = "' . $this->db->escapeString($path) . '"';
        }

        $result = $this->db->query($sql);
        if (!$result) {
            $error = $this->db->lastErrorMsg();
            throw new Exception('Cannot retrieve coverage for ' . $path.  ': ' . $error);
        }

        while ($res = $result->fetchArray(SQLITE3_ASSOC)) {
            $this->pathTotal[$res['path']]   = $res['total'];
            $this->pathCovered[$res['path']] = $res['covered'];
            $this->pathDead[$res['path']]    = $res['dead'];
            $this->coveredlines += $res['covered'];
            $this->totallines   += $res['total'];
            $this->deadlines    += $res['dead'];
        }

        return array($this->coveredlines, $this->totallines, $this->deadlines);
    }

    function retrievePathCoverage($path)
    {
        if (!$this->totallines) {
            // set up the cache
            $this->retrieveProjectCoverage($path);
        }

        if (!isset($this->pathCovered[$path])) {
            return array(0, 0, 0);
        }

        return array($this->pathCovered[$path], $this->pathTotal[$path], $this->pathDead[$path]);
    }

    function retrievePathCoverageByTest($path, $test)
    {
        $id = $this->getFileId($path);
        $testid = $this->getTestId($test);

        $sql = '
            SELECT state, COUNT(linenumber) AS ln
            FROM coverage
            WHERE files_id = ' . $id. ' AND tests_id = ' . $testid . '
            GROUP BY state';
        $result = $this->db->query($sql);
        if (!$result) {
            $error = $this->db->lastErrorMsg();
            throw new Exception('Cannot retrieve path coverage for ' . $path .
                                ' in test ' . $test . ': ' . $error);
        }

        $total = $dead = $covered = 0;
        while ($res = $result->fetchArray(SQLITE3_ASSOC)) {
            if ($res['state'] === Sqlite::COVERAGE_COVERED) {
                $covered = $res['ln'];
            }

            if ($res['state'] === Sqlite::COVERAGE_DEAD) {
                $dead = $res['ln'];
            }

            $total += $res['ln'];
        }

        return array($covered, $total, $dead);
    }

    function retrieveCoverageByTest($path, $test)
    {
        $id = $this->getFileId($path);
        $testid = $this->getTestId($test);

        $sql = 'SELECT state AS coverage, linenumber FROM coverage
                    WHERE files_id = ' . $id . ' AND tests_id = ' . $testid . '
                    ORDER BY linenumber ASC';
        $result = $this->db->query($sql);
        if (!$result) {
            $error = $this->db->lastErrorMsg();
            throw new Exception('Cannot retrieve test ' . $test .
                                ' coverage for ' . $path.  ': ' . $error);
        }

        $ret = array();
        while ($res = $result->fetchArray(SQLITE3_ASSOC)) {
            $ret[$res['linenumber']] = $res['coverage'];
        }

        return $ret;
    }

    function getFileId($path)
    {
        $sql = 'SELECT id FROM files WHERE path = "' . $this->db->escapeString($path) .'"';
        $id = $this->db->querySingle($sql);
        if ($id === false || $id === null) {
            throw new Exception('Unable to retrieve file ' . $path . ' id from database');
        }

        return $id;
    }

    function getTestId($path)
    {
        $sql = 'SELECT id FROM tests WHERE testpath = "' . $this->db->escapeString($path) . '"';
        $id = $this->db->querySingle($sql);
        if ($id === false || $id === null) {
            throw new Exception('Unable to retrieve test file ' . $path . ' id from database');
        }

        return $id;
    }

    function removeOldTest($testpath, $id = null)
    {
        if ($id === null) {
            $id = $this->getTestId($testpath);
        }

        // gather information
        $sql = 'SELECT DISTINCT files_id FROM coverage
                WHERE
                    tests_id = ' . $id ;
        if (!empty($this->deleted)) {
            $sql .= '
                AND
                    files_id NOT IN (' . implode(', ', $this->deleted) . ')';
        }

        $result = $this->db->query($sql);
        while ($res = $result->fetchArray(SQLITE3_ASSOC)) {
            $this->deleted[] = $res['files_id'];
        }

        echo "\ndeleting old test ", $testpath," .";
        $this->db->exec('DELETE FROM tests WHERE id = ' . $id);
        echo '.';
        $this->db->exec('DELETE FROM coverage WHERE tests_id = ' . $id);
        echo '.';
        $this->db->exec('DELETE FROM coverage_nonsource WHERE tests_id = ' . $id);
        echo '.';
        $p = $this->db->escapeString(str_replace('.phpt', '.xdebug', $testpath));
        $this->db->exec('DELETE FROM xdebugs WHERE path = "' . $p . '"');
        echo " done\n";
    }

    function addTest($testpath, $id = null)
    {
        try {
            $id = $this->getTestId($testpath);
            $this->db->exec('UPDATE tests SET hash = "' . md5_file($testpath) . '" WHERE id = ' . $id);
        } catch (Exception $e) {
            echo "Adding new test $testpath\n";
            $sql = 'INSERT INTO tests (testpath, hash) VALUES(:testpath, :md5)';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':testpath', $testpath);
            $stmt->bindValue(':md5', md5_file($testpath));
            $stmt->execute();
            $id = $this->db->lastInsertRowID();
        }

        $file  = str_replace('.phpt', '.xdebug', $testpath);
        $sql = 'REPLACE INTO xdebugs (path, hash) VALUES(:path, :md5)';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':path', $file);
        $stmt->bindValue(':md5', md5_file($file));
        $stmt->execute();

        return $id;
    }

    function unChangedXdebug($path)
    {
        $sql = 'SELECT hash FROM xdebugs WHERE path = "' . $this->db->escapeString($path) . '"';
        $md5 = $this->db->querySingle($sql);
        if (!$md5 || $md5 != md5_file($path)) {
            return false;
        }

        return true;
    }

    function retrieveCoverage($path)
    {
        $id = $this->getFileId($path);
        $links = $this->retrieveLineLinks($path, $id);
        $links = array_map(function ($arr) {return count($arr);}, $links);

        $sql = '
            SELECT state AS coverage, linenumber
            FROM all_lines
            WHERE files_id = ' . $id . '
            ORDER BY linenumber ASC';
        $result = $this->db->query($sql);
        if (!$result) {
            $error = $this->db->lastErrorMsg();
            throw new Exception('Cannot retrieve coverage for ' . $path.  ': ' . $error);
        }

        $return = array();
        while ($res = $result->fetchArray()) {
            if (!isset($return[$res['linenumber']])) {
                $return[$res['linenumber']] = array();
            }

            if (
                !isset($return[$res['linenumber']]['coverage']) ||
                $return[$res['linenumber']]['coverage'] !== Sqlite::COVERAGE_COVERED
            ) {
                // Found a case where a line could be dead and not covered, we still don't know why
                if (
                    isset($return[$res['linenumber']]['coverage']) &&
                    $return[$res['linenumber']]['coverage'] === Sqlite::COVERAGE_NOT_COVERED &&
                    $res['coverage'] === Sqlite::COVERAGE_DEAD
                ) {
                    continue;
                }

                $return[$res['linenumber']]['coverage'] = $res['coverage'];
            }


            if (isset($links[$res['linenumber']])) {
                $return[$res['linenumber']]['link'] = $links[$res['linenumber']];
            } else {
                $return[$res['linenumber']]['link'] = 0;
            }
        }

        return $return;
    }

    function updateTotalCoverage()
    {
        echo "Updating coverage per-file intermediate table\n";

        $sql = '
            SELECT files_id, linenumber, state
            FROM all_lines
            ORDER BY files_id, linenumber ASC';
        $result = $this->db->query($sql);
        $lines = array();
        while ($res = $result->fetchArray(SQLITE3_ASSOC)) {
            if (!isset($lines[$res['files_id']])) {
                $lines[$res['files_id']] = array();
            }

            $lines[$res['files_id']][$res['linenumber']] = $res['state'];
        }

        $ret = array();
        foreach ($lines as $file => $lines) {
            $ret[$file]['covered']     = 0;
            $ret[$file]['dead']        = 0;
            $ret[$file]['not_covered'] = 0;
            foreach (array_count_values($lines) as $state => $count) {
                if ($state === Sqlite::COVERAGE_COVERED) {
                    $ret[$file]['covered'] = $count;
                }

                if ($state === Sqlite::COVERAGE_NOT_COVERED) {
                    $ret[$file]['not_covered'] = $count;
                }

                if ($state === Sqlite::COVERAGE_DEAD) {
                    $ret[$file]['dead'] = $count;
                }
            }
        }

        foreach ($ret as $id => $line) {
            $covered     = $line['covered'];
            $dead        = $line['dead'];
            $not_covered = $line['not_covered'];
            $this->db->exec('REPLACE INTO line_info (files_id, covered, dead, total)
                            VALUES(' . $id . ',' . $covered . ',' . $dead . ',' . ($covered + $not_covered) . ')');
            echo ".";
        }

        echo "\ndone\n";
    }

    public function updateAllLines()
    {
        echo "Updating the all lines internal table\n";

        $keys = implode(', ', array_keys($this->lines));
        $sql = '
            SELECT files_id, linenumber, state
            FROM all_lines
            WHERE files_id IN (' . $keys . ')
            ORDER BY linenumber ASC';

        $result = $this->db->query($sql);
        $data = array();
        while ($res = $result->fetchArray(SQLITE3_ASSOC)) {
            if (!isset($data[$res['files_id']])) {
                $data[$res['files_id']] = array();
            }

            $data[$res['files_id']][$res['linenumber']] = $res['state'];
        }

        foreach ($data as $id => $lines) {
            foreach ($lines as $line => $state) {
                if (
                    // Only allow lines that are in the new rollout.
                    isset($this->lines[$id][$line]) ||
                    // Line already marked as covered.
                    (
                        isset($this->lines[$id][$line]) &&
                        (
                         $this->lines[$id][$line] !== Sqlite::COVERAGE_COVERED ||
                         $state > $this->lines[$id][$line]
                        )
                    )
                ) {
                    $this->lines[$id][$line] = $state;
                }
            }
        }
        unset($data);

        echo '.';
        $sql  = 'DELETE FROM all_lines WHERE files_id IN (' . $keys . ');';
        $this->db->exec($sql);

        $sql = 'INSERT INTO all_lines (files_id, linenumber, state) VALUES (:id, :line, :state);';
        $stmt = $this->db->prepare($sql);
        foreach ($this->lines as $file => $lines) {
            if (!is_array($lines)) {
                continue;
            }

            echo '.';
            foreach ($lines as $line => $state) {
                $stmt->bindValue(':id',    $file,  SQLITE3_INTEGER);
                $stmt->bindValue(':line',  $line,  SQLITE3_INTEGER);
                $stmt->bindValue(':state', $state, SQLITE3_INTEGER);
                $stmt->execute();
            }
        }

        echo "\ndone\n";
    }

    function addFile($path, $issource = 0)
    {
        $sql = 'SELECT id FROM files WHERE path = "' . $this->db->escapeString($path) . '"';
        $id = $this->db->querySingle($sql);
        if ($id === false) {
            throw new Exception('Unable to add file ' . $path . ' to database');
        }

        if ($id !== null) {
            $sql = 'UPDATE files SET hash = :md5, issource = :issource WHERE path = :path';
        } else {
            $sql = 'INSERT INTO files (path, hash, issource) VALUES(:path, :md5, :issource)';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':path',     $path);
        $stmt->bindValue(':md5',      md5_file($path));
        $stmt->bindValue(':issource', $issource);
        if (!$stmt->execute()) {
            throw new Exception('Problem running this particular SQL: ' . $sql);
        }

        if ($id === null) {
            $id = $this->db->lastInsertRowID();
        }

        return $id;
    }

    public function addNoCoverageFiles()
    {
        echo "Adding files with no coverage information\n";

        // Start by pruning out files we already have information about
        $sql = 'SELECT * FROM files WHERE issource = 1';
        $result = $this->db->query($sql);
        while ($res = $result->fetchArray(SQLITE3_ASSOC)) {
            $key = array_search($res['path'], $this->files);
            if (isset($this->files[$key])) {
                unset($this->files[$key]);
            }
        }

        $codepath = $this->codepath;
        spl_autoload_register(function($class) use ($codepath){
            $file = str_replace(array('\\', '_'), DIRECTORY_SEPARATOR, $class);
            if (file_exists($codepath . DIRECTORY_SEPARATOR . $file . '.php')) {
                include $codepath . DIRECTORY_SEPARATOR . $file . '.php';
                return true;
            }
            if ($file = stream_resolve_include_path($file . '.php')) {
                include $file;
                return true;
            }
            return false;
        });

        foreach ($this->files as $file) {
            if (empty($file)) {
                continue;
            }

            echo "$file\n";
            $id = $this->addFile($file, 1);

            // Figure out of the file has been already inclduded or not
            $included = false;

            $relative_file = substr($file, strlen($this->codepath . DIRECTORY_SEPARATOR), -4);

            // We need to try a few things here to actually find the correct class
            // Foo/Bar.php may mean Foo_Bar Foo\Bar or PEAR2\Foo\Bar
            $class       = str_replace('/', '_', $relative_file);
            $ns_class    = str_replace('/', '\\', $relative_file);
            $pear2_class = 'PEAR2\\' . $ns_class;

            $classes = array_merge(get_declared_classes(), get_declared_interfaces());

            if (in_array($class, $classes)
                || in_array($ns_class, $classes)
                || in_array($pear2_class, $classes)) {
                $included = true;
            }

            // Get basic coverage information on the file
            if ($included === false) {
                xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);
                include $file;
                $data = xdebug_get_code_coverage(true);
                $this->lines[$id] = $data[$file];
            } else {
                /*
                 * @TODO files that already have been loaded need to have 
                 * their missing coverage lines added too
                 */
            }
        }

        echo "Done\n";
    }

    function addCoverage($testpath, $testid, $xdebug)
    {
        $sql = 'DELETE FROM coverage WHERE tests_id = ' . $testid . ';
                DELETE FROM coverage_nonsource WHERE tests_id = ' . $testid;
        $worked = $this->db->exec($sql);

        echo "\n";
        foreach ($xdebug as $path => $results) {
            if (!file_exists($path)) {
                continue;
            }

            $issource = 1;
            if (
                strpos($path, $this->codepath) !== 0 ||
                strpos($path, $this->testpath) === 0
            ) {
                $issource = 0;
            }

            echo ".";
            $id = $this->addFile($path, $issource);
            $key = array_search($path, $this->files);
            if (isset($this->files[$key])) {
                unset($this->files[$key]);
            }

            if ($issource) {
                if (!isset($this->lines[$id])) {
                    $this->lines[$id] = array();
                }
            } elseif (!$issource) {
                $sql2 = 'INSERT INTO coverage_nonsource
                        (files_id, tests_id)
                        VALUES(' . $id . ', ' . $testid . ')';
                $worked = $this->db->exec($sql2);
                if (!$worked) {
                    $error = $this->db->lastErrorMsg();
                    throw new Exception('Cannot add coverage for test ' . $testpath .
                                        ', covered file ' . $path . ': ' . $error);
                }
                continue;
            }

            $sql = '';
            foreach ($results as $line => $state) {
                if (!$line) {
                    continue; // line 0 does not exist, skip this (xdebug quirk)
                }

                if ($issource) {
                    if (
                        !isset($this->lines[$id][$line]) ||
                        // Line already marked as covered.
                        $this->lines[$id][$line] !== Sqlite::COVERAGE_COVERED ||
                        $state > $this->lines[$id][$line]
                    ) {
                        $this->lines[$id][$line] = $state;
                    }
                }

                $sql .= 'INSERT INTO coverage
                    (files_id, tests_id, linenumber, state)
                    VALUES (' . $id . ', ' . $testid . ', ' . $line . ', ' . $state. ');';
            }

            if ($sql !== '') {
                $worked = $this->db->exec($sql);
                if (!$worked) {
                    $error = $this->db->lastErrorMsg();
                    throw new Exception('Cannot add coverage for test ' . $testpath .
                                        ', covered file ' . $path . ': ' . $error . "\nSQL: $sql");
                }
            }
        }
    }

    function begin()
    {
        $this->db->exec('PRAGMA synchronous=OFF'); // make inserts super fast
        $this->db->exec('BEGIN');
    }

    function commit()
    {
        $this->db->exec('COMMIT');
        $this->db->exec('PRAGMA synchronous=NORMAL'); // make inserts super fast
        echo "Compatcing the database\n";
        $this->db->exec('VACUUM');
    }

    /**
     * Retrieve a list of .phpt tests that either have been modified,
     * or the files they access have been modified
     * @return array
     */
    function getModifiedTests()
    {
        // first scan for new .phpt files
        $tests = array();
        foreach (new \RegexIterator(
                    new \RecursiveIteratorIterator(
                        new \RecursiveDirectoryIterator($this->testpath,
                                                        0|\RecursiveDirectoryIterator::SKIP_DOTS)
                    ), '/\.phpt$/') as $file
        ) {
            if (strpos((string) $file, '.svn')) {
                continue;
            }

            $tests[] = realpath((string) $file);
        }

        $newtests = array();
        foreach ($tests as $path) {
            if ($path == $this->db->querySingle('SELECT testpath FROM tests WHERE testpath = "' .
                                       $this->db->escapeString($path) . '"')) {
                continue;
            }

            $newtests[] = $path;
        }

        $modifiedTests = $modifiedPaths = array();
        $paths = $this->retrievePaths(1);
        echo "Scanning ", count($paths), " source files";
        foreach ($paths as $path) {
            echo '.';

            $sql = 'SELECT id, hash, issource FROM files WHERE path = "' . $this->db->escapeString($path) . '"';
            $result = $this->db->query($sql);
            while ($res = $result->fetchArray(SQLITE3_ASSOC)) {
                if (!file_exists($path) || md5_file($path) == $res['hash']) {
                    if ($res['issource'] && !file_exists($path)) {
                        $this->db->exec('
                            DELETE FROM files WHERE id = '. $res['id'] .';
                            DELETE FROM coverage WHERE files_id = '. $res['id'] . ';
                            DELETE FROM all_lines WHERE files_id = '. $res['id'] . ';
                            DELETE FROM line_info WHERE files_id = '. $res['id'] . ';');
                    }
                    break;
                }

                $modifiedPaths[] = $path;
                // file is modified, get a list of tests that execute this file
                if ($res['issource']) {
                    $sql = '
                        SELECT t.testpath
                        FROM coverage c, tests t
                        WHERE
                            c.files_id = ' . $res['id'] . '
                          AND
                            t.id = c.tests_id';
                } else {
                    $sql = '
                        SELECT t.testpath
                        FROM coverage_nonsource c, tests t
                        WHERE
                            c.files_id = ' . $res['id'] . '
                          AND
                            t.id = c.tests_id';
                }

                $result2 = $this->db->query($sql);
                while ($res = $result2->fetchArray(SQLITE3_NUM)) {
                    $modifiedTests[$res[0]] = true;
                }

                break;
            }
        }

        echo "done\n";
        echo count($modifiedPaths), ' modified files resulting in ',
            count($modifiedTests), " modified tests\n";
        $paths = $this->retrieveTestPaths();
        echo "Scanning ", count($paths), " test paths";
        foreach ($paths as $path) {
            echo '.';
            $sql = '
                SELECT id, hash FROM tests where testpath = "' .
                $this->db->escapeString($path) . '"';
            $result = $this->db->query($sql);
            while ($res = $result->fetchArray(SQLITE3_ASSOC)) {
                if (!file_exists($path)) {
                    $this->removeOldTest($path, $res['id']);
                    continue;
                }

                if (md5_file($path) != $res['hash']) {
                    $modifiedTests[$path] = true;
                }
            }
        }

        echo "done\n";
        echo count($newtests), ' new tests and ', count($modifiedTests), " modified tests should be re-run\n";
        return array_merge($newtests, array_keys($modifiedTests));
    }
}
