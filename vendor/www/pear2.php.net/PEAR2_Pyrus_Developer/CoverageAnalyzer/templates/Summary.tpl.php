<?php
$coverage = $context->retrieveProjectCoverage();
list($covered, $total, $dead) = $coverage;
?>
  <h2>Code Coverage Files</h2>
  <h3>Total lines: <?php echo $total; ?>, covered lines: <?php echo $covered; ?>, dead lines: <?php echo $dead; ?></h3>

    <?php
    $percent = 0;
    if ($total > 0) {
        $percent = round(($covered / $total) * 100, 1);
    }

    function getClass($percent)
    {
        if ($percent < 50) {
            return 'bad';
        } elseif ($percent < 75) {
            return 'ok';
        } else {
            return 'good';
        }
    }
    
    ?>
    <p class="<?php echo getClass($percent); ?>"><?php echo $percent; ?>% code coverage</p>
    <p>
    <a href="<?php echo $parent->context->getRootLink(); ?>?test=TOC">Code Coverage per PHPT test</a>
    </p>

  <table>
   <thead>
    <tr>
        <th>Coverage %</th>
        <th>Source File</th>
        <th>Uncovered % of total uncovered</th>
    </tr>
   </thead>
   <tbody>
   <?php foreach ($context as $sourceFile):
   list($sourceCovered, $sourceTotal, $sourceDead) = $sourceFile->coverageInfo();
   ?>
   <tr>
    <td class="<?php echo getClass($sourceFile->coveragePercentage()); ?>"><?php echo $sourceFile->coveragePercentage() . '%'; ?></td>
    <td><a href="<?php echo $parent->context->getFileLink($sourceFile->name()); ?>"><?php echo $sourceFile->shortName(); ?></a></td>
    <td><?php echo round(($sourceTotal - $sourceCovered)/($total - $covered)*100, 2); ?>%</td>
   </tr>
   <?php endforeach; ?>
   </tbody>
  </table>
