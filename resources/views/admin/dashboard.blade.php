<!doctype html>
<html lang="en">
  <!--begin::Head-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Point Of Sale System</title>
    <!--begin::Primary Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="title" content="AdminLTE v4 | Dashboard" />
    <meta name="author" content="ColorlibHQ" />
    <meta
      name="description"
      content="AdminLTE is a Free Bootstrap 5 Admin Dashboard, 30 example pages using Vanilla JS."
    />
    <meta
      name="keywords"
      content="bootstrap 5, bootstrap, bootstrap 5 admin dashboard, bootstrap 5 dashboard, bootstrap 5 charts, bootstrap 5 calendar, bootstrap 5 datepicker, bootstrap 5 tables, bootstrap 5 datatable, vanilla js datatable, colorlibhq, colorlibhq dashboard, colorlibhq admin dashboard"
    />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!--end::Primary Meta Tags-->
    <!--begin::Fonts-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
      integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
      crossorigin="anonymous"
    />
    <!--end::Fonts-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/styles/overlayscrollbars.min.css"
      integrity="sha256-tZHrRjVqNSRyWg2wbppGnT833E/Ys0DHWGwT04GiqQg="
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(OverlayScrollbars)-->
    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
      integrity="sha256-9kPW/n5nn53j4WMRYAxe9c1rCY96Oogo/MKSVdKzPmI="
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(Bootstrap Icons)-->
    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="../../dist/css/adminlte.css" />
    <!--end::Required Plugin(AdminLTE)-->
    <!-- apexcharts -->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css"
      integrity="sha256-4MX+61mt9NVvvuPjUWdUdyfZfxSB1/Rf9WtqRHgG5S0="
      crossorigin="anonymous"
    />
    <!-- jsvectormap -->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/css/jsvectormap.min.css"
      integrity="sha256-+uGLJmmTKOqBr+2E6KDYs/NRsHxSkONXFHUL0fy2O/4="
      crossorigin="anonymous"
    />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  </head>
  <!--end::Head-->

  
  <!--begin::Body-->
  <body class="layout-fixed sidebar-expand-lg bg-body-tertiary">

    <style>
      
  .modal-lg {
    max-width: 80%; /* Adjust the width as needed */
  }
  
  .modal-body {
    max-height: 70vh; /* Limit the height to prevent overflow */
    overflow-y: auto; /* Add scrolling if content exceeds the height */
  }
    </style>
    <!--begin::App Wrapper-->
    <div class="app-wrapper">
      <!--begin::Header-->
      <nav class="app-header navbar navbar-expand bg-body">
        <!--begin::Container-->
        <div class="container-fluid">
          <!--begin::Start Navbar Links-->
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                <i class="bi bi-list"></i>
              </a>
            </li>
            <!-- Trigger Button for the Modal -->
      
          
            <!-- Developer Info Modal Trigger Button -->

          </ul>
          <!--end::Start Navbar Links-->
          <!--begin::End Navbar Links-->
          <ul class="navbar-nav ms-auto">
            <!--begin::Navbar Search-->
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
                  src="../../dist/assets/img/user2-160x160.jpg"
                  class="user-image rounded-circle shadow"
                  alt="User Image"
                />
                <span class="d-none d-md-inline">{{ Auth::user()->name ?? 'Guest' }}</span>
              </a>
              <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                <!--begin::User Image-->
                <li class="user-header text-bg-primary">
                  <img
                  src="{{ asset('dist/assets/img/user2-160x160.jpg') }}"
                  class="rounded-circle shadow"
                  alt="User Image"
                />                
                  <p>
                    Leaf Light Systems- Web Developer
                    <small>POS</small>
                  </p>
                </li>
                <!--end::User Image-->
                <!--begin::Menu Body-->
                <li class="user-body">
                  <!--begin::Row-->
                  <div class="row">
                    <div class="col-4 text-center"><a href="#">Followers</a></div>
                    <div class="col-4 text-center"><a href="#">Sales</a></div>
                    <div class="col-4 text-center"><a href="#">Friends</a></div>
                  </div>
                  <!--end::Row-->
                </li>
                <!--end::Menu Body-->
                <!--begin::Menu Footer-->
                <li class="user-footer">
                  <a href="{{ route('profile.edit') }}" class="btn btn-default btn-flat">Profile</a>
                  <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-link btn-flat float-end text-decoration-none">Sign out</button>
                </form>                
                </li>
                <!--end::Menu Footer-->
              </ul>
            </li>
            <!--end::User Menu Dropdown-->
          </ul>
          <!--end::End Navbar Links-->
        </div>
        <!--end::Container-->
      </nav>
      <!--end::Header-->
      <!--begin::Sidebar-->
      <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <!--begin::Sidebar Brand-->
       
        <!--end::Sidebar Brand-->
        <!--begin::Sidebar Wrapper-->
        <div class="sidebar-wrapper">
          <nav class="mt-2">
            <!--begin::Sidebar Menu-->

            <div class="sidebar-logo text-center py-3">
              <a href="/">
                <img src="{{ asset('logo.png') }}" alt="Company Logo" 
                     class="img-fluid rounded-circle" style="max-height: 100px;">
              </a>
            </div>

            <ul
              class="nav sidebar-menu flex-column"
              data-lte-toggle="treeview"
              role="menu"
              data-accordion="false"
            >
             <!-- Sales Navigation Item -->
<li class="nav-item menu-open">
  <a href="#" class="nav-link active" data-bs-toggle="modal" data-bs-target="#salesModal">
    <i class="nav-icon bi bi-coin"></i>
    <p>Manage Sales</p>
  </a>
</li>

              <!-- Products Nav Item -->
<li class="nav-item">
 <!-- Reports Management Trigger -->
<a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#reportsModal">
  <i class="nav-icon bi bi-cart"></i>
  <p>View Reports</p>
</a>
</li>
<li class="nav-item">
 <!-- Trigger Link -->
<a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#inventoryManagementModal">
  <i class="nav-icon bi bi-receipt"></i>
  <p>Manage Inventory</p>
</a>

</li>
<li class="nav-item">
  <!-- Trigger Button -->
<a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#userManagementModal">
  <i class="nav-icon bi bi-people"></i>
  <p>Manage Users</p>
</a>
</li>
<li class="nav-item">
  <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#productModal">
    <i class="nav-icon bi bi-cart"></i>
    <p>Manage Products</p>
  </a>
</li>

<li class="nav-item">
  <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#productModal">
    <i class="nav-icon bi bi-people"></i>
    <p>View Expenses</p>
  </a>
</li>
  
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-gear"></i>
                  <p>
                    Settings
                    <span class="nav-badge badge text-bg-secondary me-3"></span>
                  
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{route('profile.edit')}}" class="nav-link">
                      <i class="nav-icon bi bi-lock"></i>
                      <p>Account Management</p>
                    </a>
                  </li>
                  <li class="nav-item">
                   <!-- Trigger Button -->
<a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#printerSettingsModal">
  <i class="nav-icon bi bi-printer"></i>
  <p>Printer Settings</p>
</a>
                  </li>
                  <li class="nav-item">
                    <!-- Trigger Button -->
<a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#exchangeRatesModal">
  <i class="nav-icon bi bi-currency-exchange"></i>
  <p>Exchange Rates</p>
</a>
                  </li>
              
                 <!-- Trigger Button -->
<li class="nav-item">
  <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#paymentMethodsModal">
    <i class="nav-icon bi bi-wallet"></i>
    <p>Payment Methods</p>
  </a>
</li>
                </ul>
              </li>
              <li class="nav-item">
              <!-- Trigger Link -->
<a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#evaluationAnalyticsModal">
  <i class="nav-icon bi bi-house-down-fill"></i>
  <p>Evaluations</p>
</a>
              </li>

              <li class="nav-item">
                <a href="{{ route('logout') }}" class="nav-link" 
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
               <i class="nav-icon bi bi-escape"></i>
               <p>Sign Out</p>
             </a>
             
             <!-- Logout Form -->
             <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
               @csrf
             </form>
             
              </li>
              <li class="nav-item">
            
            <!--end::Sidebar Menu-->
          </nav>
        </div>
        <!--end::Sidebar Wrapper-->
      </aside>
      <!--end::Sidebar-->
<!-- Sales Modal -->
<div class="modal fade" id="salesModal" tabindex="-1" aria-labelledby="salesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg"> <!-- Modal size increased using 'modal-lg' -->
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="salesModalLabel">Sales Options</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <!-- Add View Sales Button -->
        <button class="btn btn-primary w-100 my-2 d-flex align-items-center justify-content-center" onclick="window.location.href='/add-view-sales'">
          <i class="bi bi-plus-circle me-2"></i> Add View Sales
        </button>
        <!-- Manage Sales Button -->
        <button class="btn btn-secondary w-100 my-2 d-flex align-items-center justify-content-center" onclick="window.location.href='/manage-sales'">
          <i class="bi bi-tools me-2"></i> Manage Sales
        </button>
        <!-- Sales Reports Button -->
        <button class="btn btn-success w-100 my-2 d-flex align-items-center justify-content-center" onclick="window.location.href='/sales-reports'">
          <i class="bi bi-file-earmark-bar-graph me-2"></i> Sales Reports
        </button>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
          <i class="bi bi-x-circle"></i> Close
        </button>
      </div>
    </div>
  </div>
</div>


<!-- Product Management Modal -->
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="productModalLabel">Manage Products</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Form for Adding & Editing -->
        <form id="productForm" action="{{ route('products.store') }}" method="POST">
          @csrf
          <input type="hidden" name="product_id" id="product_id">
          <div class="row">
            <div class="col-md-6">
              <label class="form-label">Product Name</label>
              <input type="text" name="name" id="name" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Cost Price</label>
              <input type="number" name="cost_price" id="cost_price" class="form-control" required step="0.01">
            </div>
            <div class="col-md-6">
              <label class="form-label">Selling Price</label>
              <input type="number" name="selling_price" id="selling_price" class="form-control" required step="0.01">
            </div>
            <div class="col-md-6">
              <label class="form-label">Discount (%)</label>
              <input type="number" name="discount" id="discount" class="form-control" step="0.01">
            </div>
            <div class="col-md-6">
              <label class="form-label">Stock Quantity</label>
              <input type="number" name="stock" id="stock" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Tax</label>
              <select name="tax" id="tax" class="form-control">
                <option value="15%">15%</option>
                <option value="0%">0%</option>
                <option value="Exempt">Exempt</option>
              </select>
            </div>
            <div class="col-12 mt-3">
              <label class="form-label">Description</label>
              <textarea name="description" id="description" class="form-control"></textarea>
            </div>
          </div>
          <div class="modal-footer mt-3">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save Product</button>
            <button type="button" class="btn btn-success">View All Products</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>


          
<script>
  document.addEventListener("DOMContentLoaded", function() {
    fetchProducts();

    function fetchProducts() {
        fetch("{{ route('products.list') }}")
        .then(response => response.json())
        .then(data => {
            let tableBody = document.getElementById("productTable");
            tableBody.innerHTML = ""; // Clear existing data
            
            data.forEach(product => {
                let row = `
                    <tr>
                        <td>${product.name}</td>
                        <td>${product.cost_price}</td>
                        <td>${product.selling_price}</td>
                        <td>${product.stock}</td>
                        <td>${product.tax}</td>
                        <td>
                            <button class="btn btn-warning btn-sm edit-btn" data-id="${product.id}">Edit</button>
                            <button class="btn btn-danger btn-sm delete-btn" data-id="${product.id}">Delete</button>
                        </td>
                    </tr>
                `;
                tableBody.innerHTML += row;
            });

            attachEventListeners();
        })
        .catch(error => console.error("Error fetching products:", error));
    }

    function attachEventListeners() {
        document.querySelectorAll(".edit-btn").forEach(button => {
            button.addEventListener("click", function() {
                let productId = this.getAttribute("data-id");
                editProduct(productId);
            });
        });

        document.querySelectorAll(".delete-btn").forEach(button => {
            button.addEventListener("click", function() {
                let productId = this.getAttribute("data-id");
                deleteProduct(productId);
            });
        });
    }

    function editProduct(id) {
        // Fetch and populate product data in the form
    }

    function deleteProduct(id) {
        if (confirm("Are you sure you want to delete this product?")) {
            fetch(`/products/delete/${id}`, { method: "DELETE" })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                fetchProducts(); // Refresh table
            });
        }
    }
});

</script>

  
  

 


<!-- View Products Modal -->
<div class="modal fade" id="viewProductsModal" tabindex="-1" aria-labelledby="viewProductsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewProductsModalLabel">View Products</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <table class="table table-striped table-hover">
          <thead>
            <tr>
              <th>#</th>
              <th>Product Name</th>
              <th>Order Price</th>
              <th>Selling Price</th>
              <th>Tax (%)</th>
              <th>Stock</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="productTableBody">
            <!-- Dynamic rows will be inserted here -->
            <tr>
              <td>1</td>
              <td>Sample Product</td>
              <td>$10</td>
              <td>$15</td>
              <td>5%</td>
              <td>100</td>
              <td>
                <button class="btn btn-sm btn-primary me-2"><i class="bi bi-pencil"></i> Edit</button>
                <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i> Delete</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <a href="{{route('admin.dashboard')}}" class="btn btn-warning"  aria-label="Close">Close</a>
      </div>
    </div>
  </div>
</div>


<!-- Customer Management Modal -->
<div class="modal fade" id="customerManagementModal" tabindex="-1" aria-labelledby="customerManagementModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="customerManagementModalLabel">Customer Management</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Add Customer Form -->
        <form id="addCustomerForm">
          <div class="mb-3">
            <label for="customerName" class="form-label">Customer Name</label>
            <input type="text" class="form-control" id="customerName" placeholder="Enter customer name" required>
          </div>
          <div class="mb-3">
            <label for="customerEmail" class="form-label">Customer Email</label>
            <input type="email" class="form-control" id="customerEmail" placeholder="Enter customer email" required>
          </div>
          <div class="mb-3">
            <label for="customerPhone" class="form-label">Phone Number</label>
            <input type="tel" class="form-control" id="customerPhone" placeholder="Enter customer phone number" required>
          </div>
          <div class="mb-3">
            <label for="customerAddress" class="form-label">Address</label>
            <textarea class="form-control" id="customerAddress" rows="3" placeholder="Enter customer address"></textarea>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-success">Add Customer</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </form>

        
      </div>
    </div>
  </div>
</div>

<!-- Modal for Website Links -->
<div class="modal fade" id="websiteLinksModal" tabindex="-1" aria-labelledby="websiteLinksModalLabel" aria-hidden="true">
  <div class="modal-dialog">
      <div class="modal-content">
          <!-- Modal Header -->
          <div class="modal-header">
              <h5 class="modal-title" id="websiteLinksModalLabel">Website Links</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <!-- Modal Body -->
          <div class="modal-body">
              <ul class="list-group">
                  <li class="list-group-item">
                      <a href="https://www.leaflightsystems.com" target="_blank">Visit Leaf Light Systems Website</a>
                  </li>
                  <li class="list-group-item">
                      <a href="https://www.example.com" target="_blank">Visit Example Website</a>
                  </li>
                  <li class="list-group-item">
                      <a href="https://www.anotherwebsite.com" target="_blank">Visit Another Website</a>
                  </li>
              </ul>
          </div>
          <!-- Modal Footer -->
          <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
      </div>
  </div>
</div>


<!-- Developer Info Modal -->
<div class="modal fade" id="developerInfoModal" tabindex="-1" aria-labelledby="developerInfoModalLabel" aria-hidden="true">
  <div class="modal-dialog">
      <div class="modal-content">
          <!-- Modal Header -->
          <div class="modal-header">
              <h5 class="modal-title" id="developerInfoModalLabel">Developer Information</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <!-- Modal Body -->
          <div class="modal-body">
              <p><strong>Name:</strong> John Doe</p>
              <p><strong>Email:</strong> john.doe@example.com</p>
              <p><strong>Phone:</strong> +1 234 567 890</p>
              <p><strong>Website:</strong> <a href="https://www.example.com" target="_blank">www.example.com</a></p>
              <p><strong>Skills:</strong> Laravel, PHP, JavaScript, Bootstrap, MySQL</p>
              <p><strong>About:</strong> John is a passionate web developer with 5+ years of experience in building scalable applications.</p>
          </div>
          <!-- Modal Footer -->
          <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
      </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="evaluationAnalyticsModal" tabindex="-1" aria-labelledby="evaluationAnalyticsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <!-- Modal Header -->
      <div class="modal-header">
        <h5 class="modal-title" id="evaluationAnalyticsModalLabel">
          <i class="bi bi-graph-up-arrow"></i> Evaluation Analytics
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <!-- Modal Body -->
      <div class="modal-body">
        <div class="row">
          <!-- Sales & Profits Chart -->
          <div class="col-md-6 mb-4">
            <canvas id="salesProfitsChart"></canvas>
          </div>
          <!-- Stock and Breakages Chart -->
          <div class="col-md-6 mb-4">
            <canvas id="stockBreakagesChart"></canvas>
          </div>
          <!-- High-Demand Products Pie Chart -->
          <div class="col-md-6 mb-4">
            <canvas id="highDemandProductsChart"></canvas>
          </div>
          <!-- Losses Line Chart -->
          <div class="col-md-6 mb-4">
            <canvas id="lossesChart"></canvas>
          </div>
        </div>
      </div>
      <!-- Modal Footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="bi bi-x-circle"></i> Close
        </button>
      </div>
    </div>
  </div>
</div>




<!-- Modal -->
<div class="modal fade" id="printerSettingsModal" tabindex="-1" aria-labelledby="printerSettingsModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <!-- Modal Header -->
      <div class="modal-header">
        <h5 class="modal-title" id="printerSettingsModalLabel">
          <i class="bi bi-printer"></i> Printer Settings
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <!-- Modal Body -->
      <div class="modal-body">
        <form>
          <!-- Printer Selection -->
          <div class="mb-3">
            <label for="printerSelect" class="form-label">Select Printer</label>
            <select id="printerSelect" class="form-select">
              <option selected>Choose a printer...</option>
              <option>Printer 1</option>
              <option>Printer 2</option>
              <option>Printer 3</option>
            </select>
          </div>
          <!-- Paper Size Selection -->
          <div class="mb-3">
            <label for="paperSizeSelect" class="form-label">Paper Size</label>
            <select id="paperSizeSelect" class="form-select">
              <option selected>Choose paper size...</option>
              <option>A4</option>
              <option>A5</option>
              <option>Letter</option>
              <option>Legal</option>
            </select>
          </div>
          <!-- Orientation Selection -->
          <div class="mb-3">
            <label class="form-label">Orientation</label>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="orientation" id="orientationPortrait" value="portrait" checked>
              <label class="form-check-label" for="orientationPortrait">Portrait</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="orientation" id="orientationLandscape" value="landscape">
              <label class="form-check-label" for="orientationLandscape">Landscape</label>
            </div>
          </div>
        </form>
      </div>
      <!-- Modal Footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="bi bi-x-circle"></i> Close
        </button>
        <button type="button" class="btn btn-primary">
          <i class="bi bi-check-circle"></i> Save Settings
        </button>
      </div>
    </div>
  </div>
</div>


<!-- Modal -->
<div class="modal fade" id="exchangeRatesModal" tabindex="-1" aria-labelledby="exchangeRatesModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <!-- Modal Header -->
      <div class="modal-header">
        <h5 class="modal-title" id="exchangeRatesModalLabel">
          <i class="bi bi-currency-exchange"></i> Exchange Rates
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <!-- Modal Body -->
      <div class="modal-body">
        <form id="exchangeRatesForm">
          <!-- USD Rate -->
          <div class="mb-3">
            <label for="usdRate" class="form-label">USD Rate</label>
            <input type="number" class="form-control" id="usdRate" placeholder="Enter rate for USD" step="0.01">
          </div>
          <!-- ZWG Rate -->
          <div class="mb-3">
            <label for="zwgRate" class="form-label">ZWG Rate</label>
            <input type="number" class="form-control" id="zwgRate" placeholder="Enter rate for ZWG" step="0.01">
          </div>
          <!-- Rand Rate -->
          <div class="mb-3">
            <label for="randRate" class="form-label">Rand Rate</label>
            <input type="number" class="form-control" id="randRate" placeholder="Enter rate for Rand" step="0.01">
          </div>
        </form>
      </div>
      <!-- Modal Footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="bi bi-x-circle"></i> Close
        </button>
        <button type="button" class="btn btn-primary" id="saveRatesButton">
          <i class="bi bi-check-circle"></i> Save Rates
        </button>
      </div>
    </div>
  </div>
</div>


<!-- Inventory Management Modal -->
<div class="modal fade" id="inventoryManagementModal" tabindex="-1" aria-labelledby="inventoryManagementModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
      <div class="modal-content">
          <!-- Modal Header -->
          <div class="modal-header">
              <h5 class="modal-title" id="inventoryManagementModalLabel">
                  <i class="bi bi-receipt"></i> Inventory Management
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <!-- Modal Body -->
          <div class="modal-body">
              <!-- Search Bar -->
              <div class="mb-3 d-flex justify-content-between">
                  <input type="text" class="form-control me-2" id="searchInventory" placeholder="Search inventory...">
                  <button type="button" class="btn btn-primary">
                      <i class="bi bi-search"></i> Search
                  </button>
              </div>

             
<!-- Inventory Table -->
<h6 class="mt-4">Current Inventory</h6>
<table class="table table-striped table-bordered mt-2">
    <thead>
        <tr>
            <th>Id</th>
            <th>Item Name</th>
            <th>Description</th>
            <th>Selling Price</th>
            <th>Stock Quantity</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($productss as $index => $product)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $product->name }}</td>
                <td>{{ $product->description }}</td>
                <td>${{ number_format($product->selling_price, 2) }}</td>
                <td>{{ $product->stock }}</td>
                <td>
                  <button class="btn btn-warning btn-sm" onclick="editProduct({{ $product->id }}, '{{ $product->name }}', '{{ $product->price }}')">
                    <i class="bi bi-pencil-square"></i> Edit
                </button>
                  <button class="btn btn-danger btn-sm" onclick="deleteProduct({{ $product->id }})">
                      <i class="bi bi-trash3"></i> Delete
                  </button> 
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function editProduct(productId, name, selling_price, cost_price, discount, stock) {
        Swal.fire({
            title: 'Edit Product',
            html: `
                <input id="swal-product-name" class="swal2-input" placeholder="Product Name" value="${name}">
                <input id="swal-selling-price" class="swal2-input" placeholder="Selling Price" type="number" value="${selling_price}">
                <input id="swal-cost-price" class="swal2-input" placeholder="Cost Price" type="number" value="${cost_price}">
                <input id="swal-discount" class="swal2-input" placeholder="Discount (%)" type="number" value="${discount}">
                <input id="swal-stock-quantity" class="swal2-input" placeholder="Stock Quantity" type="number" value="${stock}">
            `,
            showCancelButton: true,
            confirmButtonText: 'Save Changes',
            preConfirm: () => {
                const name = document.getElementById('swal-product-name').value;
                const selling_price = document.getElementById('swal-selling-price').value;
                const cost_price = document.getElementById('swal-cost-price').value;
                const discount = document.getElementById('swal-discount').value;
                const stock_quantity = document.getElementById('swal-stock-quantity').value;
                
                if (!name || !selling_price || !cost_price || !discount || !stock_quantity) {
                    Swal.showValidationMessage('All fields are required');
                    return false;
                }

                return { name, selling_price, cost_price, discount, stock_quantity };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                updateProduct(productId, result.value);
            }
        });
    }

    function updateProduct(productId, data) {
        fetch(`/products/${productId}`, {
            method: "PUT",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                "Content-Type": "application/json"
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire('Updated!', 'Product has been updated.', 'success').then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error!', 'Failed to update product.', 'error');
            }
        })
        .catch(error => console.error("Error:", error));
    }
</script>


<script>
  function deleteProduct(productId) {
      if (confirm("Are you sure you want to delete this product?")) {
          fetch(`/products/${productId}`, {
              method: "DELETE",
              headers: {
                  "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                  "Content-Type": "application/json"
              }
          })
          .then(response => response.json())
          .then(data => {
              if (data.success) {
                  alert("Product deleted successfully.");
                  location.reload(); // Refresh page or remove the row dynamically
              } else {
                  alert("Error deleting product.");
              }
          })
          .catch(error => console.error("Error:", error));
      }
  }
</script>


              <!-- Pagination -->
              <nav aria-label="Inventory Pagination">
                  <ul class="pagination justify-content-center mt-3">
                      <li class="page-item disabled">
                          <a class="page-link" href="#" tabindex="-1" aria-disabled="true">
                              <i class="bi bi-chevron-double-left"></i>
                          </a>
                      </li>
                      <li class="page-item active">
                          <a class="page-link" href="#">1</a>
                      </li>
                      <li class="page-item">
                          <a class="page-link" href="#">2</a>
                      </li>
                      <li class="page-item">
                          <a class="page-link" href="#">3</a>
                      </li>
                      <li class="page-item">
                          <a class="page-link" href="#">
                              <i class="bi bi-chevron-double-right"></i>
                          </a>
                      </li>
                  </ul>
              </nav>
          </div>

          <!-- Modal Footer -->
          <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                  <i class="bi bi-x-circle"></i> Close
              </button>

            <button type="button" class="btn btn-success" onclick="window.open('{{ route('inventory.print') }}', '_blank')">
                <i class="bi bi-printer"></i> Print Inventory Report
            </button>
          </div>
      </div>
  </div>
</div>

<!-- Reports Management Modal -->
<div class="modal fade" id="reportsModal" tabindex="-1" aria-labelledby="reportsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <!-- Modal Header -->
      <div class="modal-header">
        <h5 class="modal-title" id="reportsModalLabel">
          <i class="bi bi-graph-up-arrow"></i> Report Management
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <!-- Modal Body -->
      <div class="modal-body">
        <p class="text-muted">Select a report type and specify the parameters to generate your desired report.</p>
        
        <!-- Report Type Selection -->
        <form id="reportForm">
          <div class="mb-3">
            <label for="reportType" class="form-label">Report Type</label>
            <select class="form-select" id="reportType" required>
              <option value="" disabled selected>Select report type</option>
              <option value="stock">Stock Report</option>
              <option value="financial">Financial Report</option>
              <option value="sales">Sales Report</option>
            </select>
          </div>

          <!-- Till Operator Selection (Only for Sales Report) -->
          <div class="mb-3" id="tillOperatorField" style="display: none;">
            <label for="tillOperator" class="form-label">Till Operator</label>
            <select class="form-select" id="tillOperator">
              <option value="all" selected>All Till Operators</option>
              <option value="operator1">Operator 1</option>
              <option value="operator2">Operator 2</option>
              <option value="operator3">Operator 3</option>
            </select>
          </div>

          <!-- Date Range Selection -->
          <div class="row g-3">
            <div class="col-md-6">
              <label for="startDate" class="form-label">Start Date</label>
              <input type="date" class="form-control" id="startDate" required>
            </div>
            <div class="col-md-6">
              <label for="endDate" class="form-label">End Date</label>
              <input type="date" class="form-control" id="endDate" required>
            </div>
          </div>

          <!-- Additional Options -->
          <div class="mb-3 mt-3">
            <label for="format" class="form-label">Report Format</label>
            <select class="form-select" id="format" required>
              <option value="" disabled selected>Select format</option>
              <option value="pdf">PDF</option>
              <option value="excel">Excel</option>
            </select>
          </div>
        </form>

        <!-- Report List Table -->
        <h6 class="mt-4">Generated Reports</h6>
        <table class="table table-striped table-bordered mt-2">
          <thead>
            <tr>
              <th>#</th>
              <th>Report Name</th>
              <th>Type</th>
              <th>Date Generated</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="reportsTableBody">
            <!-- Example Rows -->
            <tr>
              <td>1</td>
              <td>Stock Report - Jan 2024</td>
              <td>Stock</td>
              <td>2024-01-15</td>
              <td>
                <button class="btn btn-success btn-sm">
                  <i class="bi bi-download"></i> Download
                </button>
                <button class="btn btn-danger btn-sm">
                  <i class="bi bi-trash"></i> Delete
                </button>
              </td>
            </tr>
            <!-- More rows will be dynamically added -->
          </tbody>
        </table>
      </div>

      <!-- Modal Footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="bi bi-x-circle"></i> Close
        </button>
        <button type="submit" class="btn btn-primary" form="reportForm">
          <i class="bi bi-file-earmark-plus"></i> Generate Report
        </button>
      </div>
    </div>
  </div>
</div>

<!-- JavaScript to Show/Hide Till Operator Field -->
<script>
  document.getElementById("reportType").addEventListener("change", function () {
    const tillOperatorField = document.getElementById("tillOperatorField");
    tillOperatorField.style.display = this.value === "sales" ? "block" : "none";
  });
</script>



<!-- Modal -->
<div class="modal fade" id="paymentMethodsModal" tabindex="-1" aria-labelledby="paymentMethodsModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <!-- Modal Header -->
      <div class="modal-header">
        <h5 class="modal-title" id="paymentMethodsModalLabel">
          <i class="bi bi-wallet"></i> Payment Methods
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <!-- Modal Body -->
      <div class="modal-body">
        <form id="paymentMethodsForm">
          <!-- USD Payment Method -->
          <div class="mb-3 form-check form-switch">
            <input class="form-check-input" type="checkbox" id="usdPaymentMethod" checked>
            <label class="form-check-label" for="usdPaymentMethod">Enable USD Payment</label>
          </div>
          <!-- ZWG Payment Method -->
          <div class="mb-3 form-check form-switch">
            <input class="form-check-input" type="checkbox" id="zwgPaymentMethod">
            <label class="form-check-label" for="zwgPaymentMethod">Enable ZWG Payment</label>
          </div>
          <!-- Rand Payment Method -->
          <div class="mb-3 form-check form-switch">
            <input class="form-check-input" type="checkbox" id="randPaymentMethod">
            <label class="form-check-label" for="randPaymentMethod">Enable Rand Payment</label>
          </div>
        </form>
      </div>
      <!-- Modal Footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="bi bi-x-circle"></i> Close
        </button>
        <button type="button" class="btn btn-success" id="savePaymentMethodsButton">
          <i class="bi bi-check-circle"></i> Save Settings
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="userManagementModal" tabindex="-1" aria-labelledby="userManagementModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <!-- Modal Header -->
      <div class="modal-header">
        <h5 class="modal-title" id="userManagementModalLabel">
          <i class="bi bi-people"></i> User Management
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <!-- Modal Body -->
      <div class="modal-body">
        <div class="table-responsive">
          <!-- User Management Table -->
          <table class="table table-striped table-hover">
            <thead>
              <tr>
                <th>User ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($users as $user)
              <tr>
                <td>{{ $user->id }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                  @if($user->user_type == 1)
                    Administrator
                  @elseif($user->user_type == 2)
                    Supervisor
                  @else
                    Cashier
                  @endif
                </td>
                <td>
                  <button class="btn btn-sm btn-primary"><i class="bi bi-pencil-square"></i> Edit</button>
              <button class="btn btn-sm btn-danger delete-user" data-id="{{ $user->id }}">
                    <i class="bi bi-trash"></i> Delete
              </button> 
               <button class="btn btn-sm btn-warning reset-password" data-id="{{ $user->id }}">
                  <i class="bi bi-key"></i> Reset Password
              </button>                
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      <!-- Modal Footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="bi bi-x-circle"></i> Close
        </button>
        <button type="button" class="btn btn-success">
          <i class="bi bi-plus-circle"></i> Add User
        </button>
             
      </div>
    </div>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
  $('#resetPasswordModal').on('shown.bs.modal', function () {
    $('#newPassword').focus();
});

    $(document).on('click', '.delete-user', function(e) {
        e.preventDefault();
        let userId = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/users/' + userId,
                    type: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire('Deleted!', response.message, 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON.message, 'error');
                    }
                });
            }
        });
    });
</script>
<style>
modal {
  z-index: 1050 !important;
  display: block;
  pointer-events: auto;
}
  </style>



<!-- Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordLabel" aria-hidden="true">
  <div class="modal-dialog">
      <div class="modal-content">
          <div class="modal-header">
              <h5 class="modal-title" id="resetPasswordLabel">Reset User Password</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <input type="hidden" id="resetUserId">
              <div class="mb-3">
                  <label for="newPassword" class="form-label">New Password</label>
                  <input type="password" class="form-control" id="newPassword" placeholder="Enter new password" autofocus>
              </div>
              <div class="mb-3">
                  <label for="confirmPassword" class="form-label">Confirm Password</label>
                  <input type="password" class="form-control" id="confirmPassword" placeholder="Enter new password" autofocus>
              </div>
          </div>
          <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="button" class="btn btn-primary" id="confirmResetPassword">Reset Password</button>
          </div>
      </div>
  </div>
</div>

<script>
  $(document).on('click', '.reset-password', function() {
      let userId = $(this).data('id');
      $('#resetUserId').val(userId);
      $('#resetPasswordModal').modal('show');
  });

  $('#confirmResetPassword').click(function() {
      let userId = $('#resetUserId').val();
      let newPassword = $('#newPassword').val();
      let confirmPassword = $('#confirmPassword').val();

      if (newPassword !== confirmPassword) {
          Swal.fire('Error', 'Passwords do not match!', 'error');
          return;
      }

      $.ajax({
    url: '/users/' + userId + '/reset-password',
    type: 'POST',
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    data: {
        new_password: newPassword,
        new_password_confirmation: confirmPassword
    },
    success: function(response) {
        if (response.status === 'success') {
            Swal.fire('Success', response.message, 'success').then(() => {
                $('#resetPasswordModal').modal('hide');
            });
        } else {
            Swal.fire('Error', response.message, 'error');
        }
    },
    error: function(xhr) {
        Swal.fire('Error', xhr.responseJSON.message, 'error');
    }
});

  });
</script>



          <!-- Modal Body -->
          <div class="modal-body">
          
      <!--begin::App Main-->
      <main class="app-main">
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <div class="col-sm-6"><h3 class="mb-0">Admin Dashboard</h3></div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="#">Home</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Admin Dashboard</li>
                </ol>
              </div>
            </div>
            <!--end::Row-->
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content Header-->
        <!--begin::App Content-->
        <div class="app-content">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <!--begin::Col-->
              <div class="col-lg-3 col-6">
                <!--begin::Small Box Widget 1-->
                <div class="small-box text-bg-primary">
                  <div class="inner">
                    <h3>150</h3>
                    <p>Users</p>
                  </div>
                  <svg
                    class="small-box-icon"
                    fill="currentColor"
                    viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true"
                  >
                    <path
                      d="M2.25 2.25a.75.75 0 000 1.5h1.386c.17 0 .318.114.362.278l2.558 9.592a3.752 3.752 0 00-2.806 3.63c0 .414.336.75.75.75h15.75a.75.75 0 000-1.5H5.378A2.25 2.25 0 017.5 15h11.218a.75.75 0 00.674-.421 60.358 60.358 0 002.96-7.228.75.75 0 00-.525-.965A60.864 60.864 0 005.68 4.509l-.232-.867A1.875 1.875 0 003.636 2.25H2.25zM3.75 20.25a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0zM16.5 20.25a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0z"
                    ></path>
                  </svg>
                 
                </div>
                <!--end::Small Box Widget 1-->
              </div>
              <!--end::Col-->
              <div class="col-lg-3 col-6">
                <!--begin::Small Box Widget 2-->
                <div class="small-box text-bg-success">
                  <div class="inner">
                    <h3>{{ number_format($total_profit, 2) }}  USD</h3>
                    <p>Stock Profit</p>
                  </div>
                  <svg
                    class="small-box-icon"
                    fill="currentColor"
                    viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true"
                  >
                    <path
                      d="M18.375 2.25c-1.035 0-1.875.84-1.875 1.875v15.75c0 1.035.84 1.875 1.875 1.875h.75c1.035 0 1.875-.84 1.875-1.875V4.125c0-1.036-.84-1.875-1.875-1.875h-.75zM9.75 8.625c0-1.036.84-1.875 1.875-1.875h.75c1.036 0 1.875.84 1.875 1.875v11.25c0 1.035-.84 1.875-1.875 1.875h-.75a1.875 1.875 0 01-1.875-1.875V8.625zM3 13.125c0-1.036.84-1.875 1.875-1.875h.75c1.036 0 1.875.84 1.875 1.875v6.75c0 1.035-.84 1.875-1.875 1.875h-.75A1.875 1.875 0 013 19.875v-6.75z"
                    ></path>
                  </svg>
                 
                </div>
                <!--end::Small Box Widget 2-->
              </div>
              <!--end::Col-->
              <div class="col-lg-3 col-6">
                <!--begin::Small Box Widget 3-->
                <div class="small-box text-bg-warning">
                  <div class="inner">
                    <h3>44</h3>
                    <p>Till Operators</p>
                  </div>
                  <svg
                    class="small-box-icon"
                    fill="currentColor"
                    viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true"
                  >
                    <path
                      d="M6.25 6.375a4.125 4.125 0 118.25 0 4.125 4.125 0 01-8.25 0zM3.25 19.125a7.125 7.125 0 0114.25 0v.003l-.001.119a.75.75 0 01-.363.63 13.067 13.067 0 01-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 01-.364-.63l-.001-.122zM19.75 7.5a.75.75 0 00-1.5 0v2.25H16a.75.75 0 000 1.5h2.25v2.25a.75.75 0 001.5 0v-2.25H22a.75.75 0 000-1.5h-2.25V7.5z"
                    ></path>
                  </svg>
                 
                </div>
                <!--end::Small Box Widget 3-->
              </div>
              <!--end::Col-->
              <div class="col-lg-3 col-6">
                <!--begin::Small Box Widget 4-->
                <div class="small-box text-bg-dark">
  <div class="inner">
    <h3 id="current-time">--:--:--</h3>
    <p>Current Time</p>
  </div>
  <svg
    class="small-box-icon"
    fill="currentColor"
    viewBox="0 0 24 24"
    xmlns="http://www.w3.org/2000/svg"
    aria-hidden="true"
  >
    <path
      clip-rule="evenodd"
      fill-rule="evenodd"
      d="M2.25 13.5a8.25 8.25 0 018.25-8.25.75.75 0 01.75.75v6.75H18a.75.75 0 01.75.75 8.25 8.25 0 01-16.5 0z"
    ></path>
    <path
      clip-rule="evenodd"
      fill-rule="evenodd"
      d="M12.75 3a.75.75 0 01.75-.75 8.25 8.25 0 018.25 8.25.75.75 0 01-.75.75h-7.5a.75.75 0 01-.75-.75V3z"
    ></path>
  </svg>
</div>

<script>
  function updateTime() {
    const now = new Date();
    const timeString = now.toLocaleTimeString();
    document.getElementById("current-time").textContent = timeString;
  }

  setInterval(updateTime, 1000); // Update time every second
  updateTime(); // Initial call to display time immediately
</script>

                <!--end::Small Box Widget 4-->
              </div>
              <!--end::Col-->
            </div>
            <!--end::Row-->
            <!--begin::Row-->
            <div class="row">
                <!-- /.card -->
                   <!-- /.card-body -->
                  <div class="card-footer">
                    <form action="#" method="post">
                      <div class="input-group">
                        
                        {{-- <span class="input-group-append"> --}}
                        
                        </span>
                      </div>
                    </form>
                  </div>
                  <!-- /.card-footer-->
                </div>
                <!-- /.direct-chat -->
              </div>
            
                  </div>
                </div>
              </div>
              <!-- /.Start col -->
            </div>
            <!-- /.row (main row) -->
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content-->
      </main>
      <!--end::App Main-->
      <!--begin::Footer-->
      <footer class="app-footer">
        <!--begin::To the end-->
        <div class="float-end d-none d-sm-inline">Powered By Leaf Light Systems</div>
        <!--end::To the end-->
        <!--begin::Copyright-->
      </footer>
      <!--end::Footer-->
    </div>
    <!--end::App Wrapper-->
    <!--begin::Script-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script
      src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/browser/overlayscrollbars.browser.es6.min.js"
      integrity="sha256-dghWARbRe2eLlIJ56wNB+b760ywulqK3DzZYEpsg2fQ="
      crossorigin="anonymous"
    ></script>
    <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
      integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
      integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
    <script src="../../dist/js/adminlte.js"></script>
    <!--end::Required Plugin(AdminLTE)--><!--begin::OverlayScrollbars Configure-->
    <script>
      const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
      const Default = {
        scrollbarTheme: 'os-theme-light',
        scrollbarAutoHide: 'leave',
        scrollbarClickScroll: true,
      };
      document.addEventListener('DOMContentLoaded', function () {
        const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
        if (sidebarWrapper && typeof OverlayScrollbarsGlobal?.OverlayScrollbars !== 'undefined') {
          OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
            scrollbars: {
              theme: Default.scrollbarTheme,
              autoHide: Default.scrollbarAutoHide,
              clickScroll: Default.scrollbarClickScroll,
            },
          });
        }
      });
    </script>
    <!--end::OverlayScrollbars Configure-->
    <!-- OPTIONAL SCRIPTS -->
    <!-- sortablejs -->
    <script
      src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"
      integrity="sha256-ipiJrswvAR4VAx/th+6zWsdeYmVae0iJuiR+6OqHJHQ="
      crossorigin="anonymous"
    ></script>
    <!-- sortablejs -->
    <script>
      const connectedSortables = document.querySelectorAll('.connectedSortable');
      connectedSortables.forEach((connectedSortable) => {
        let sortable = new Sortable(connectedSortable, {
          group: 'shared',
          handle: '.card-header',
        });
      });

      const cardHeaders = document.querySelectorAll('.connectedSortable .card-header');
      cardHeaders.forEach((cardHeader) => {
        cardHeader.style.cursor = 'move';
      });
    </script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    // Sales & Profits Bar Chart
    new Chart(document.getElementById('salesProfitsChart'), {
      type: 'bar',
      data: {
        labels: ['January', 'February', 'March', 'April'],
        datasets: [
          { label: 'Sales', data: [3000, 4000, 5000, 7000], backgroundColor: 'rgba(54, 162, 235, 0.7)' },
          { label: 'Profits', data: [1000, 1500, 2000, 2500], backgroundColor: 'rgba(75, 192, 192, 0.7)' }
        ]
      },
      options: { responsive: true }
    });

    // Stock & Breakages Doughnut Chart
    new Chart(document.getElementById('stockBreakagesChart'), {
      type: 'doughnut',
      data: {
        labels: ['Stocks', 'Breakages'],
        datasets: [{
          data: [90, 10],
          backgroundColor: ['rgba(75, 192, 192, 0.7)', 'rgba(255, 99, 132, 0.7)']
        }]
      },
      options: { responsive: true }
    });

    // High-Demand Products Pie Chart
    new Chart(document.getElementById('highDemandProductsChart'), {
      type: 'pie',
      data: {
        labels: ['Product A', 'Product B', 'Product C'],
        datasets: [{
          data: [40, 30, 30],
          backgroundColor: ['rgba(255, 205, 86, 0.7)', 'rgba(153, 102, 255, 0.7)', 'rgba(255, 159, 64, 0.7)']
        }]
      },
      options: { responsive: true }
    });

    // Losses Line Chart
    new Chart(document.getElementById('lossesChart'), {
      type: 'line',
      data: {
        labels: ['January', 'February', 'March', 'April'],
        datasets: [{
          label: 'Losses',
          data: [500, 700, 600, 800],
          borderColor: 'rgba(255, 99, 132, 1)',
          backgroundColor: 'rgba(255, 99, 132, 0.2)',
          fill: true
        }]
      },
      options: { responsive: true }
    });
  });
</script>
    <!-- apexcharts -->
    <script
      src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"
      integrity="sha256-+vh8GkaU7C9/wbSLIcwq82tQ2wTf44aOHA8HlBMwRI8="
      crossorigin="anonymous"
    ></script>
    {{-- modal  --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
    <!-- jsvectormap -->
    <script
      src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/js/jsvectormap.min.js"
      integrity="sha256-/t1nN2956BT869E6H4V1dnt0X5pAQHPytli+1nTZm2Y="
      crossorigin="anonymous"
    ></script>
    <script
      src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/maps/world.js"
      integrity="sha256-XPpPaZlU8S/HWf7FZLAncLg2SAkP8ScUTII89x9D3lY="
      crossorigin="anonymous"
    ></script>
  </body>
  <!--end::Body-->
</html>
