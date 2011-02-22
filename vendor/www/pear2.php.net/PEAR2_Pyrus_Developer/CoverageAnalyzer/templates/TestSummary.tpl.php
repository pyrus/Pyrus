  <h2>Tests Executed, click for code coverage summary</h2>
  <p>
   <a href="/workspace/PEAR2/Pyrus_Developer/www/CoverageAnalyzer/">Aggregate Code Coverage for all tests</a>
  </p>
  <ul>
   <?php foreach ($context as $test): ?>
   <li>
    <a href="<?php echo $parent->context->getTOClink($test); ?>"><?php echo str_replace($context->testpath . '/', '', $test); ?></a>
   </li>
   <?php endforeach; ?>
  </ul>