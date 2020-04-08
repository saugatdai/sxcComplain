<?php
namespace sxc;

require 'Views/Sections.php';
use sxc\Views\Sections;
Sections::generateHeader("SXC Multimedia Reporting System");

?>

<h2 id="index">SXC Technical Resource Monitoring System</h2>
<nav id='navigation'>
	<ul>
		<li>
			<a href="complain.php">
				<span class='big'>Complain</span>
				<br />
				<span class='small'>For All Users</span>
			</a>
		</li>
		<li>
			<a href="cds.php">
				<span class='big'>CDS</span>
				<br />
				<span class='small'>For Computer Department Staffs(CDS) Only</span>
			</a>
		</li>
		<li>
			<a href="admin.php">
				<span class='big'>Administrator</span>
				<br />
				<span class='small'>For Administrators of this system only</span>
			</a>
		</li>
	</ul>
</nav>

<?php
Sections::generateFooter();
?>