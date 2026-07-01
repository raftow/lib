<?php
/**
 * @var string $placeholder
 * @var string $lang_input
 * @var string $cols
 * @var string $rows
 * @var string $onchange
 * @var string $input_style
 * @var string $spell_check
 * @var string $input_required
 * @var string $input_disabled

 */

 // if(!$lang_input)  $lang_input = "en";
?>
<textarea placeholder="<?php echo $placeholder ?>"
    class="form-control <?php echo $lang_input ?> form-area <?php echo $css_class ?>"
    cols="<?php echo $cols ?>"
    rows="<?php echo $rows ?>"
    id="<?php echo $col_name ?>"
    name="<?php echo $col_name ?>"
    dir="<?php echo $dir ?>"
    onchange="<?php echo $onchange ?>"
    <?php echo $input_style ?>
    <?php echo $spell_check ?>
    <?php echo $input_required ?>
    <?php echo $input_disabled ?>><?php echo $val ?></textarea>