# StackWirtz WordPress Plugin

A WordPress plugin to retrieve data from a CSV file using a shortcode.

## Features

- CSV data search and display using shortcodes
- Admin settings page with CSV file management
- Data Dashboard with interactive Chart.js visualizations
- Twig templating for clean UI rendering
- VPN detection and access control
- Automatic newest CSV file detection
- User authentication and role-based access

## Settings Page

The plugin includes an admin settings page accessible via **Wirtz Data Settings** in the WordPress admin menu. The settings page allows you to:

- Configure CSV folder path
- View current CSV files in the folder (filename, size, modification date)
- Set Ollama API endpoint and model
- Configure redirect page after login
- Manage allowed NetIDs

## Data Dashboard

Access interactive charts and visualizations via **Wirtz Data Settings → Data Dashboard**. The dashboard includes:

- Summary statistics (total people, productions, years)
- Current data file information (name, last modified, row count)
- Productions by Year (bar chart)
- Career Distribution (doughnut chart - UG vs Grad)
- Team Distribution (pie chart)
- Top Roles (horizontal bar chart)
- Graduation Years (line chart)

## CSV File Management

The plugin automatically detects and uses the newest CSV file in the configured folder based on modification time. The settings page displays all CSV files currently in the folder for easy monitoring.

## Architecture

- **Twig Templates**: Located in `src/templates/` for clean separation of logic and presentation
- **Form Handlers**: Separate classes in `src/handlers/` for processing form submissions
- **Models**: Data models in `src/Models/` for CSV data handling
- **VPN Detection**: JavaScript-based IP detection for access control