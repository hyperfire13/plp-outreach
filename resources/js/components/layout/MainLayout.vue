<template>
  <div class="app-wrapper">
      <nav class="app-header navbar navbar-expand bg-body">
        <div class="container-fluid">
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link" href="#" role="button" @click.prevent="toggleSidebar">
                <i class="bi bi-list"></i>
              </a>
            </li>
            <li class="nav-item d-none d-md-block">
              <a href="#" class="nav-link">Home</a>
            </li>
            <li class="nav-item d-none d-md-block">
              <a href="#" class="nav-link">Contact</a>
            </li>
          </ul>
          <ul class="navbar-nav ms-auto">
            <!--begin::Navbar Search-->
            <li class="nav-item">
              <a class="nav-link" data-widget="navbar-search" href="#" role="button">
                <i class="bi bi-search"></i>
              </a>
            </li>
            <!--end::Navbar Search-->

            <!--begin::Fullscreen Toggle-->
            <li class="nav-item">
              <a class="nav-link" href="#" data-lte-toggle="fullscreen">
                <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
                <i data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display: none"></i>
              </a>
            </li>
            <!--end::Fullscreen Toggle-->

            <!--begin::User Menu Dropdown-->
            <li class="nav-item dropdown user-menu">
              <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                <img
                  src=""
                  class="user-image rounded-circle shadow"
                  alt="User Image"
                />
                <span class="d-none d-md-inline">Alexander Pierce</span>
              </a>
              <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                <!--begin::Menu Footer-->
                <li class="user-footer">
                  <a href="#" class="btn btn-outline-secondary">Profile</a>
                  <a href="#" class="btn btn-outline-danger float-end">Sign out</a>
                </li>
                <!--end::Menu Footer-->
              </ul>
            </li>
            <!--end::User Menu Dropdown-->
          </ul>
        </div>
      </nav>
      <!-- SIDEBAR -->
       <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <!--begin::Sidebar Brand-->
        <div class="sidebar-brand">
          <!--begin::Brand Link-->
          <a href="./index.html" class="brand-link">
            <!--begin::Brand Image-->
            <!-- <img
              src=""
              alt="AdminLTE Logo"
              class="brand-image opacity-75 shadow"
            /> -->
            <!--end::Brand Image-->
            <!--begin::Brand Text-->
            <span class="brand-text fw-light">PLP Outreach</span>
            <!--end::Brand Text-->
          </a>
          <!--end::Brand Link-->
        </div>
        <!--end::Sidebar Brand-->
        <!--begin::Sidebar Wrapper-->
        <div class="sidebar-wrapper">
          <nav class="mt-2">
            <!--begin::Sidebar Menu-->
            <ul
              class="nav sidebar-menu flex-column"
              data-lte-toggle="treeview"
              role="navigation"
              aria-label="Main navigation"
              data-accordion="false"
              id="navigation"
            >
            <!-- Dashboard -->
            <li class="nav-item">
                <router-link to="/" class="nav-link">
                    <i class="nav-icon bi bi-speedometer2"></i>
                    <p>Dashboard</p>
                </router-link>
            </li>

            <!-- Settings -->
            <li class="nav-item menu-open">
                <a href="#" class="nav-link" @click.prevent="toggleSettings">
                    <i class="nav-icon bi bi-gear"></i>
                    <p>
                        Settings
                        <i class="nav-arrow bi bi-chevron-right"></i>
                    </p>
                </a>

                <ul class="nav nav-treeview"  v-show="settingsOpen">

                    <li class="nav-item" :class="{ 'menu-open': settingsOpen }">
                        <router-link to="/users" class="nav-link">
                            <i class="nav-icon bi bi-people"></i>
                            <p>Users</p>
                        </router-link>
                    </li>

                    <li class="nav-item">
                        <router-link to="/colleges" class="nav-link">
                            <i class="nav-icon bi bi-building"></i>
                            <p>Colleges</p>
                        </router-link>
                    </li>

                    <li class="nav-item">
                        <router-link to="/outreach-programs" class="nav-link">
                            <i class="nav-icon bi bi-megaphone"></i>
                            <p>Outreach Programs</p>
                        </router-link>
                    </li>

                </ul>
            </li>
              <li class="nav-item">
                <a href="./generate/theme.html" class="nav-link">
                  <i class="nav-icon bi bi-palette"></i>
                  <p>Theme Generate</p>
                </a>
              </li>

            </ul>
            <!--end::Sidebar Menu-->
          </nav>
        </div>
        <!--end::Sidebar Wrapper-->
      </aside>

      <main class="app-main">
        <slot />
      </main>


  </div>
</template>

<script setup>
  import { ref } from 'vue'
  const settingsOpen = ref(false)
  const toggleSidebar = () => {
    document.body.classList.toggle('sidebar-collapse')
  }
  const toggleSettings = () => {
    settingsOpen.value = !settingsOpen.value
  }
</script>
