<?php
namespace Pyrus\Developer\CoverageAnalyzer {
class Sqlite
{
    protected $db;
    protected $totallines = 0;
    protected $coveredlines = 0;
    protected $pathCovered = array();
    protected $pathTotal = array();
    public $codepath;
    public $testpath;

    function __construct($path = ':memory:', $codepath = null, $testpath = null)
    {
        $this->db = new \Sqlite3($path);

        $sql = 'SELECT version FROM analyzerversion';
        if (@$this->db->querySingle($sql) == '5.0.0') {
            $this->codepath = $this->db->querySingle('SELECT codepath FROM paths');
            $this->testpath = $this->db->querySingle('SELECT testpath FROM paths');
            return;
        }
        // restart the database
        echo "Upgrading database to version 5.0.0";
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
        $this->db->exec('VACUUM;');

        echo ".";
        $this->db->exec('BEGIN');

        $query = '
          CREATE TABLE coverage (
            files_id integer NOT NULL,
            tests_id integer NOT NULL,
            linenumber INTEGER NOT NULL,
            PRIMARY KEY (files_id, linenumber, tests_id)
          );

          CREATE TABLE all_lines (
            files_id integer NOT NULL,
            linenumber INTEGER NOT NULL,
            PRIMARY KEY (files_id, linenumber)
          );

          CREATE TABLE line_info (
            files_id integer NOT NULL,
            covered INTEGER NOT NULL,
            total INTEGER NOT NULL,
            PRIMARY KEY (files_id)
          );
          ';
        $worked = $this->db->exec($query);
        if (!$worked) {
            @$this->db->exec('ROLLBACK');
            $error = $this->db->lastErrorMsg();
            throw new Exception('Unable to create Code Coverage SQLite3 database: ' . $error);
        }

        echo ".";
        $query = '
          CREATE TABLE coverage_nonsource (
            files_id integer NOT NULL,
            tests_id integer NOT NULL,
            PRIMARY KEY (files_id, tests_id)
          );
          ';
        $worked = $this->db->exec($query);
        if (!$worked) {
            @$this->db->exec('ROLLBACK');
            $error = $this->db->lastErrorMsg();
            throw new Exception('Unable to create Code Coverage SQLite3 database: ' . $error);
        }

        echo ".";
        $query = '
          CREATE TABLE files (
            id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
            filepath TEXT(500) NOT NULL,
            filepathmd5 TEXT(32) NOT NULL,
            issource BOOL NOT NULL,
            UNIQUE (filepath)
          );
          CREATE INDEX files_issource on files (issource);
          ';
        $worked = $this->db->exec($query);
        if (!$worked) {
            @$this->db->exec('ROLLBACK');
            $error = $this->db->lastErrorMsg();
            throw new Exception('Unable to create Code Coverage SQLite3 database: ' . $error);
        }

        echo ".";
        $query = '
          CREATE TABLE xdebugs (
            xdebugpath TEXT(500) NOT NULL,
            xdebugpathmd5 TEXT(32) NOT NULL,
            PRIMARY KEY (xdebugpath)
          );';
        $worked = $this->db->exec($query);
        if (!$worked) {
            @$this->db->exec('ROLLBACK');
            $error = $this->db->lastErrorMsg();
            throw new Exception('Unable to create Code Coverage SQLite3 database: ' . $error);
        }

        echo ".";
        $query = '
          CREATE TABLE tests (
            id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
            testpath TEXT(500) NOT NULL,
            testpathmd5 TEXT(32) NOT NULL,
            UNIQUE (testpath)
          );';
        $worked = $this->db->exec($query);
        if (!$worked) {
            @$this->db->exec('ROLLBACK');
            $error = $this->db->lastErrorMsg();
            throw new Exception('Unable to create Code Coverage SQLite3 database: ' . $error);
        }

        echo ".";
        $query = '
          CREATE TABLE analyzerversion (
            version TEXT(5) NOT NULL
          );

          INSERT INTO analyzerversion VALUES("5.0.0");

          CREATE TABLE paths (
            codepath TEXT NOT NULL,
            testpath TEXT NOT NULL
          );';
        $worked = $this->db->exec($query);
        if (!$worked) {
            @$this->db->exec('ROLLBACK');
            $error = $this->db->lastErrorMsg();
            throw new Exception('Unable to create Code Coverage SQLite3 database: ' . $error);
        }

        echo ".";
        $query = '
          INSERT INTO paths VALUES(
            "' . $this->db->escapeString($codepath) . '",
            "' . $this->db->escapeString($testpath). '");';
        $worked = $this->db->exec($query);
        if (!$worked) {
            @$this->db->exec('ROLLBACK');
            $error = $this->db->lastErrorMsg();
            throw new Exception('Unable to create Code Coverage SQLite3 database: ' . $error);
        }
        $this->db->exec('COMMIT');
        echo "done\n";
    }

    function retrieveLineLinks($file)
    {
        $id = $this->getFileId($file);
        $query = 'SELECT t.testpath, c.linenumber
            FROM
                coverage c, tests t
            WHERE
                c.files_id=' . $id . ' AND t.id=c.tests_id';
        $result = $this->db->query($query);
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
        $query = 'SELECT testpath from tests ORDER BY testpath';
        $result = $this->db->query($query);
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
            $query = 'SELECT DISTINCT filepath
                FROM coverage_nonsource c, files
                WHERE c.tests_id=' . $id . '
                    AND files.id=c.files_id
                GROUP BY c.files_id
                ORDER BY filepath';
            $result = $this->db->query($query);
            if (!$result) {
                $error = $this->db->lastErrorMsg();
                throw new Exception('Cannot retrieve file paths for test ' . $test . ':' . $error);
            }
            while ($res = $result->fetchArray(SQLITE3_NUM)) {
                $ret[] = $res[0];
            }
        }
        $query = 'SELECT DISTINCT filepath
            FROM coverage c, files
            WHERE c.tests_id=' . $id . '
                AND files.id=c.files_id
            GROUP BY c.files_id
            ORDER BY filepath';
        $result = $this->db->query($query);
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
            $query = 'SELECT filepath from files ORDER BY filepath';
        } else {
            $query = 'SELECT filepath from files WHERE issource=1 ORDER BY filepath';
        }
        $result = $this->db->query($query);
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
        return round(($coverage[0] / $coverage[1]) * 100);
    }

    function retrieveProjectCoverage()
    {
        if ($this->totallines) {
            return array($this->coveredlines, $this->totallines);
        }
        $query = 'SELECT covered, total, filepath FROM line_info, files
                WHERE files.id=line_info.files_id';
        $result = $this->db->query($query);
        if (!$result) {
            $error = $this->db->lastErrorMsg();
            throw new Exception('Cannot retrieve coverage for ' . $path.  ': ' . $error);
        }
        while ($res = $result->fetchArray(SQLITE3_ASSOC)) {
            $this->pathTotal[$res['filepath']] = $res['total'];
            $this->totallines += $res['total'];
            $this->pathCovered[$res['filepath']] = $res['covered'];
            $this->coveredlines += $res['covered'];
        }

        return array($this->coveredlines, $this->totallines);
    }

    function retrievePathCoverage($path)
    {
        if (!$this->totallines) {
            // set up the cache
            $this->retrieveProjectCoverage();
        }
        if (!isset($this->pathCovered[$path])) {
            return array(0, 0);
        }
        return array($this->pathCovered[$path], $this->pathTotal[$path]);
    }

    function retrievePathCoverageByTest($path, $test)
    {
        $id = $this->getFileId($path);
        $testid = $this->getTestId($test);

        $query = 'SELECT COUNT(linenumber)
            FROM all_lines
            WHERE files_id=' . $id;
        $result = $this->db->query($query);
        if (!$result) {
            $error = $this->db->lastErrorMsg();
            throw new Exception('Cannot retrieve path coverage for ' . $path .
                                ' in test ' . $test . ': ' . $error);
        }

        $ret = array();
        while ($res = $result->fetchArray(SQLITE3_NUM)) {
            $total = $res[0];
        }

        $query = 'SELECT COUNT(linenumber)
            FROM coverage
            WHERE files_id=' . $id. ' AND tests_id=' . $testid;
        $result = $this->db->query($query);
        if (!$result) {
            $error = $this->db->lastErrorMsg();
            throw new Exception('Cannot retrieve path coverage for ' . $path .
                                ' in test ' . $test . ': ' . $error);
        }

        $ret = array();
        while ($res = $result->fetchArray(SQLITE3_NUM)) {
            $covered = $res[0];
        }
        return array($covered, $total);
    }

    function retrieveCoverageByTest($path, $test)
    {
        $id = $this->getFileId($path);
        $testid = $this->getTestId($test);

        $query = 'SELECT 1 as coverage, linenumber FROM coverage
                    WHERE files_id=' . $id . ' AND tests_id=' . $testid;
        $result = $this->db->query($query);
        if (!$result) {
            $error = $this->db->lastErrorMsg();
            throw new Exception('Cannot retrieve test ' . $test .
                                ' coverage for ' . $path.  ': ' . $error);
        }

        $ret = array();
        while ($res = $result->fetchArray(SQLITE3_ASSOC)) {
            $ret[$res['linenumber']] = $res['coverage'];
        }
        $query = 'SELECT linenumber
            FROM all_lines
            WHERE files_id=' . $id;
        $result = $this->db->query($query);
        if (!$result) {
            $error = $this->db->lastErrorMsg();
            throw new Exception('Cannot retrieve path coverage for ' . $path .
                                ' in test ' . $test . ': ' . $error);
        }

        while ($res = $result->fetchArray(SQLITE3_ASSOC)) {
            if (isset($ret[$res['linenumber']])) {
                continue;
            }
            $ret[$res['linenumber']] = 0;
        }
        return $ret;
    }

    function getFileId($path)
    {
        $query = 'SELECT id FROM files WHERE filepath=:filepath';
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':filepath', $path);
        if (!($result = $stmt->execute())) {
            throw new Exception('Unable to retrieve file ' . $path . ' id from database');
        }
        while ($id = $result->fetchArray(SQLITE3_NUM)) {
            return $id[0];
        }
        throw new Exception('Unable to retrieve file ' . $path . ' id from database');
    }

    function getTestId($path)
    {
        $query = 'SELECT id FROM tests WHERE testpath=:filepath';
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':filepath', $path);
        if (!($result = $stmt->execute())) {
            throw new Exception('Unable to retrieve test file ' . $path . ' id from database');
        }
        while ($id = $result->fetchArray(SQLITE3_NUM)) {
            return $id[0];
        }
        throw new Exception('Unable to retrieve test file ' . $path . ' id from database');
    }

    function removeOldTest($testpath, $id = null)
    {
        if ($id === null) {
            $id = $this->getTestId($testpath);
        }
        echo "deleting old test ", $testpath,'.';
        $this->db->exec('DELETE FROM tests WHERE id=' . $id);
        echo '.';
        $this->db->exec('DELETE FROM coverage WHERE tests_id=' . $id);
        echo '.';
        $this->db->exec('DELETE FROM coverage_nonsource WHERE tests_id=' . $id);
        echo '.';
        $this->db->exec('DELETE FROM xdebugs WHERE xdebugpath="' .
                        $this->db->escapeString(str_replace('.phpt', '.xdebug', $testpath)) . '"');
        echo "done\n";
    }

    function addTest($testpath, $id = null)
    {
        try {
            $id = $this->getTestId($testpath);
            $this->db->exec('UPDATE tests SET testpathmd5="' . md5_file($testpath) . '" WHERE id=' . $id);
        } catch (Exception $e) {
            echo "Adding new test $testpath\n";
            $query = 'REPLACE INTO tests
                (testpath, testpathmd5)
                VALUES(:testpath, :md5)';
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':testpath', $testpath);
            $md5 = md5_file($testpath);
            $stmt->bindValue(':md5', $md5);
            $stmt->execute();
            $id = $this->db->lastInsertRowID();
        }

        $query = 'REPLACE INTO xdebugs
            (xdebugpath, xdebugpathmd5)
            VALUES(:testpath, :md5)';
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':testpath', str_replace('.phpt', '.xdebug', $testpath));
        $md5 = md5_file(str_replace('.phpt', '.xdebug', $testpath));
        $stmt->bindValue(':md5', $md5);
        $stmt->execute();
        return $id;
    }

    function unChangedXdebug($path)
    {
        $query = 'SELECT xdebugpathmd5 FROM xdebugs
                    WHERE xdebugpath=:path';
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':path', $path);
        $result = $stmt->execute();
        if (!$result) {
            return false;
        }
        $md5 = 0;
        while ($res = $result->fetchArray(SQLITE3_NUM)) {
            $md5 = $res[0];
        }
        if (!$md5) {
            return false;
        }
        if ($md5 == md5_file($path)) {
            return true;
        }
        return false;
    }

    function getTotalCoverage($file, $linenumber)
    {
        $query = 'SELECT COUNT(linenumber) FROM coverage
                    WHERE files_id=' . $this->getFileId($file) . ' AND linenumber=' . $linenumber;
        $result = $this->db->query($query);
        if (!$result) {
            return false;
        }
        $coverage = 0;
        while ($res = $result->fetchArray(SQLITE3_NUM)) {
            $coverage = $res[0];
        }
        return $coverage;
    }

    function retrieveCoverage($path)
    {
        $id = $this->getFileId($path);
        $links = $this->retrieveLineLinks($path);
        $links = array_map(function ($arr) {return count($arr);}, $links);

        $query = 'SELECT linenumber FROM all_lines
                    WHERE files_id=' . $this->getFileId($path);
        $result = $this->db->query($query);
        if (!$result) {
            $error = $this->db->lastErrorMsg();
            throw new Exception('Cannot retrieve coverage for ' . $path.  ': ' . $error);
        }
        $coverage = 0;
        while ($res = $result->fetchArray(SQLITE3_NUM)) {
            if (!isset($links[$res[0]])) {
                $links[$res[0]] = 0;
            }
        }
        return $links;
    }

    /**
     * This is used to get the coverage which is then inserted into our
     * intermediate coverage_per_file table to speed things up at rendering
     */
    function retrieveSlowCoverage($id)
    {
        $query = 'SELECT COUNT(*) as coverage, linenumber FROM coverage WHERE files_id=' . $id . '
                    GROUP BY linenumber UNION
                  SELECT 0 as coverage, linenumber FROM all_lines WHERE files_id=' . $id . '
                    GROUP BY linenumber
                  ORDER BY linenumber';
        $result = $this->db->query($query);
        if (!$result) {
            $error = $this->db->lastErrorMsg();
            throw new Exception('Cannot retrieve coverage for ' . $path.  ': ' . $error);
        }

        $ret = array();
        while ($res = $result->fetchArray(SQLITE3_ASSOC)) {
            $ret[$res['linenumber']] = $res['coverage'];
        }
        return $ret;
    }

    function updateTotalCoverage()
    {
        echo "Updating coverage per-file intermediate table\n";
        $query = 'SELECT COUNT(DISTINCT linenumber), files_id FROM coverage GROUP BY files_id';
        $result = $this->db->query($query);
        echo ".";
        if (!$result) {
            $error = $this->db->lastErrorMsg();
            throw new Exception('Cannot retrieve coverage for ' . $path.  ': ' . $error);
        }

        $ret = array();
        while ($res = $result->fetchArray(SQLITE3_NUM)) {
            $ret[$res[1]]['covered'] = $res[0];
        }

        $query = 'SELECT COUNT(linenumber), files_id FROM all_lines GROUP BY files_id';
        $result = $this->db->query($query);
        echo ".";
        if (!$result) {
            $error = $this->db->lastErrorMsg();
            throw new Exception('Cannot retrieve coverage for ' . $path.  ': ' . $error);
        }

        while ($res = $result->fetchArray(SQLITE3_NUM)) {
            $ret[$res[1]]['total'] = $res[0];
        }
        echo ".";

        foreach ($ret as $id => $lineinfo) {
            $this->db->exec('REPLACE INTO line_info (files_id, covered, total)
                            VALUES(' . $id . ',' . $lineinfo['covered'] . ',' . $lineinfo['total'] . ')');
            echo ".";
        }
        
        echo "done\n";
    }

    function updateAllLines($id, $results)
    {
        $query = 'SELECT linenumber FROM all_lines WHERE files_id=' . $id . ' ORDER BY linenumber ASC';
        $result = $this->db->query($query);
        $lines = array();
        while ($res = $result->fetchArray(SQLITE3_NUM)) {
            $lines[] = $res[0];
        }
        $new = array_diff($results, $lines);
        $old = array_diff($lines, $results);
        if (count($new) || count($old)) {
            foreach ($new as $line) {
                if (!$line) {
                    continue; // line 0 does not exist, skip this (xdebug quirk)
                }
                $query = 'INSERT INTO all_lines (files_id, linenumber) VALUES (' . $id . ',' . $line . ')';
                $this->db->exec($query);
            }
            if (count($old)) {
                $query = 'DELETE FROM all_lines WHERE files_id=' . $id .
                    ' AND linenumber IN (' . implode(',', $old) . ')';
                $this->db->exec($query);
                $query = 'DELETE FROM coverage WHERE files_id=' . $id .
                    ' AND linenumber IN (' . implode(',', $old) . ')';
                $this->db->exec($query);
            }
        }
    }

    function addFile($filepath, $issource = 0, $results = array())
    {
        $query = 'SELECT id FROM files WHERE filepath=:filepath';
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':filepath', $filepath);
        if (!($result = $stmt->execute())) {
            throw new Exception('Unable to add file ' . $filepath . ' to database');
        }
        while ($id = $result->fetchArray(SQLITE3_NUM)) {
            if ($issource) {
                $this->updateAllLines($id[0], $results);
            }
            $query = 'UPDATE files SET filepathmd5=:md5 WHERE filepath=:filepath';
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':filepath', $filepath);
            $md5 = md5_file($filepath);
            $stmt->bindParam(':md5', $md5);
            if (!$stmt->execute()) {
                throw new Exception('Unable to update file ' . $filepath . ' md5 in database');
            }
            return $id[0];
        }
        $query = 'INSERT INTO files
            (filepath, filepathmd5, issource)
            VALUES(:testpath, :md5, :issource)';
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':testpath', $filepath);
        $md5 = md5_file($filepath);
        $stmt->bindValue(':md5', $md5);
        $stmt->bindValue(':issource', $issource);
        if (!$stmt->execute()) {
            throw new Exception('Unable to add file ' . $filepath . ' to database');
        }
        $id = $this->db->lastInsertRowID();
        if ($issource) {
            $this->updateAllLines($id, $results);
        }
        return $id;
    }

    function addCoverage($testpath, $testid, $xdebug)
    {
        $query = 'DELETE FROM coverage WHERE tests_id=' . $testid . ';
                  DELETE FROM coverage_nonsource WHERE tests_id=' . $testid;
        $worked = $this->db->exec($query);
        foreach ($xdebug as $path => $results) {
            if (!file_exists($path)) {
                continue;
            }
            if (strpos($path, $this->codepath) !== 0) {
                $issource = 0;
            } else {
                if (strpos($path, $this->testpath) === 0) {
                    $issource = 0;
                } else {
                    $issource = 1;
                }
            }
            echo ".";
            $id = $this->addFile($path, $issource, array_keys($results));
            if (!$issource) {
                $query = 'REPLACE INTO coverage_nonsource
                    (files_id, tests_id)
                    VALUES(' . $id . ', ' . $testid . ')';
                $worked = $this->db->exec($query);
                if (!$worked) {
                    $error = $this->db->lastErrorMsg();
                    throw new Exception('Cannot add coverage for test ' . $testpath .
                                        ', covered file ' . $path . ': ' . $error);
                }
                continue;
            }
            foreach ($results as $line => $info) {
                if (!$line) {
                    continue; // line 0 does not exist, skip this (xdebug quirk)
                }
                if ($info < 0) {
                    continue;
                }
                $query = 'REPLACE INTO coverage
                    (files_id, linenumber, tests_id)
                    VALUES(' . $id . ', ' . $line . ', ' . $testid  . ')';

                $worked = $this->db->exec($query);
                if (!$worked) {
                    $error = $this->db->lastErrorMsg();
                    throw new Exception('Cannot add coverage for test ' . $testpath .
                                        ', covered file ' . $path . ': ' . $error);
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
        foreach (new \RegexIterator(
                                    new \RecursiveIteratorIterator(
                                        new \RecursiveDirectoryIterator($this->testpath,
                                                                        0|\RecursiveDirectoryIterator::SKIP_DOTS)),
                                    '/\.phpt$/') as $file) {
            if (strpos((string) $file, '.svn')) {
                continue;
            }
            $tests[] = realpath((string) $file);
        }
        $newtests = array();
        foreach ($tests as $path) {
            if ($path == $this->db->querySingle('SELECT testpath FROM tests WHERE testpath="' .
                                       $this->db->escapeString($path) . '"')) {
                continue;
            }
            $newtests[] = $path;
        }

        $modifiedPaths = array();
        $modifiedTests = array();
        $paths = $this->retrievePaths(1);
        echo "Scanning ", count($paths), " source files";
        foreach ($paths as $path) {
            echo '.';
            $query = '
                SELECT id, filepathmd5, issource FROM files where filepath="' .
                $this->db->escapeString($path) . '"';
            $result = $this->db->query($query);
            while ($res = $result->fetchArray(SQLITE3_ASSOC)) {
                if (!file_exists($path) || md5_file($path) == $res['filepathmd5']) {
                    if ($res['issource'] && !file_exists($path)) {
                        echo 'here';
                        $this->db->exec('
                            DELETE FROM files WHERE id='. $res['id'] .';
                            DELETE FROM coverage WHERE files_id='. $res['id'] . ';
                            DELETE FROM all_lines WHERE files_id='. $res['id'] . ';
                            DELETE FROM line_info WHERE files_id='. $res['id'] . ';');
                    }
                    break;
                }
                $modifiedPaths[] = $path;
                // file is modified, get a list of tests that execute this file
                if ($res['issource']) {
                    $query = '
                        SELECT t.testpath
                        FROM coverage c, tests t
                        WHERE
                            c.files_id=' . $res['id'] . '
                            AND t.id=c.tests_id';
                    $result2 = $this->db->query($query);
                    while ($res = $result2->fetchArray(SQLITE3_NUM)) {
                        $modifiedTests[$res[0]] = true;
                    }
                } else {
                    $query = '
                        SELECT t.testpath
                        FROM coverage_nonsource c, tests t
                        WHERE
                            c.files_id=' . $res['id'] . '
                            AND t.id=c.tests_id';
                    $result2 = $this->db->query($query);
                    while ($res = $result2->fetchArray(SQLITE3_NUM)) {
                        $modifiedTests[$res[0]] = true;
                    }
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
            $query = '
                SELECT id, testpathmd5 FROM tests where testpath="' .
                $this->db->escapeString($path) . '"';
            $result = $this->db->query($query);
            while ($res = $result->fetchArray(SQLITE3_ASSOC)) {
                if (!file_exists($path)) {
                    $this->removeOldTest($path, $res['id']);
                    continue;
                }
                if (md5_file($path) != $res['testpathmd5']) {
                    $modifiedTests[$path] = true;
                }
            }
        }
        echo "done\n";
        echo count($newtests), ' new tests and ', count($modifiedTests), " modified tests should be re-run\n";
        return array_merge($newtests, array_keys($modifiedTests));
    }
}
}
?>