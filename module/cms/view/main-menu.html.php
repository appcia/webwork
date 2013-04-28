<div class="well sidebar-nav">

    <ul class="nav nav-list">

        <li class="nav-header">Main menu</li>
        <li><a href="<?= $this->routeUrl('cms-page-home') ?>">Dashboard</a></li>
        <li><a href="<?= $this->routeUrl('cms-user-logout') ?>">Logout</a></li>

        <li class="nav-header">Administration tools</li>
        <li><a href="<?= $this->routeUrl('cms-group-list') ?>">Groups</a></li>
        <li><a href="<?= $this->routeUrl('cms-user-list') ?>">Users</a></li>

    </ul>

</div>