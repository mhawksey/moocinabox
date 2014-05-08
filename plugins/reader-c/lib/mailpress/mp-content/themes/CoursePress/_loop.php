<table <?php $this->classes('nopmb cp_ctable'); ?>>
  <tr>
    <td <?php $this->classes('cp_section_head'); ?>>News <small>(<a href="<?php echo site_url(); ?>/course/category/course-information">Visit on site</a>)</small></td>
  </tr>
  <?php $info = false; ?>
  <tr>
    <td <?php $this->classes('nopmb cp_ctd'); ?>><div <?php $this->classes('cp_recent_act'); ?>><strong>Recent Activity</strong> (numbers in brackets indicate new posts)
        <ul>
          <?php include('includes/reader_activity.php'); ?>
          <?php //reader_activity(); ?>
        </ul>
      </div>
      <?php include('includes/news.php'); ?>
      <?php //new_news(); ?>
      </td>
  </tr>
  <tr>
    <td <?php $this->classes('nopmb cp_section_head'); ?>>Activity Stream <small>(<a href="<?php echo site_url(); ?>/activity">Visit on site</a>)</small></td>
  </tr>
  <tr>
    <td <?php $this->classes('nopmb cp_ctd'); ?>><? $object = 'status';
		 $max = 10; ?>
      <?php include('includes/bp-activity.php'); ?>
    </td>
  </tr>
  <tr>
    <td <?php $this->classes('cp_section_head'); ?>>Participant Blog Posts <small>(<a href="<?php echo site_url(); ?>/category/blog-posts">Visit on site</a>)</small></td>
  </tr>
  <tr>
    <td>
    	<?php $category_name = 'blog-post'; ?>
		<?php include('includes/two_column_output.php'); ?>
    	<?php //two_column_output('blog-posts'); ?>
    </td>
  </tr>
</table>