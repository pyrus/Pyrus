<?php
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
  <h2>Code Coverage Files for test <?php echo str_replace($context->sqlite->testpath . '/', '', $context->test); ?></h2>
  <ul>
   <?php foreach ($context as $file): ?>
   <li>
    <div class="<?php echo getClass($file->coveragePercentage()); ?>"><?php echo ' Coverage: ' . str_pad($file->coveragePercentage() . '%', 4, ' ', STR_PAD_LEFT); ?></div>
    <a href="<?php echo $parent->context->getFileLink($file->name(), $context->test); ?>"><?php echo $file->shortName(); ?></a>
   </li>
   <?php endforeach; ?>
  </ul>