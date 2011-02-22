<?php
$info = $context->coverageInfo();
$sourceCode = $context->source();

$total = count($sourceCode);
?>
 <h2>Code Coverage for <?php echo $context->shortName(); ?></h2>
 <h3>Coverage: <?php echo $context->coveragePercentage(); ?>% (Covered lines / Executable lines)</h3>
 <p><strong><?php echo $total; ?></strong> total lines, of which <strong><?php echo $info[1]; ?></strong> are executable, <strong><?php echo $info[2]; ?></strong> are dead and <strong><?php echo ($total - $info[2] - $info[1]); ?></strong> are non-executable lines</p>
 <p>Of those <strong><?php echo $info[1]; ?></strong> executable lines there are <strong><?php echo $info[0]; ?></strong> lines covered with tests and <strong><?php echo ($info[1] - $info[0]); ?></strong> lack coverage</p>

 <p><a href="<?php echo $parent->context->getRootLink(); ?>">Aggregate Code Coverage for all tests</a></p>
 <pre>
<?php foreach ($context->source() as $num => $line):
 
 $coverage = $context->coverage($num);
 echo '<span class="ln">';
    echo str_pad($num, 8, ' ', STR_PAD_LEFT);
    echo '</span>';
    
    if ($coverage === false) {
        echo (str_pad(': ', 13, ' ', STR_PAD_LEFT) . htmlentities($line));
        continue;
    }
    
    echo '<span ';
    $cov = is_array($coverage) ? $coverage['coverage'] : $coverage;
    if ($cov === -2) {
        echo 'class="dead">';
        echo '           ';
    } elseif ($cov < 1) {
        echo 'class="nc">';
        echo '           ';
    } else {
        echo 'class="cv">';
        if (!isset($context->coverage)) {
            echo '<a href="'.$parent->context->getFileLink($context->name(), null, $num).'">';
        }
    
        $text = is_string($coverage) ? $coverage : $coverage['link'];
        echo str_pad($text, 10, ' ', STR_PAD_LEFT) . ' ';
        if (!isset($context->coverage)) {
            echo '</a>';
        }
    }
    
    echo ': ' .  htmlentities($line);
    echo '</span>';
 endforeach; ?>
 </pre>