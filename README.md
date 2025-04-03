# WirtzData Plugin

## Description
The WirtzData plugin allows users to interact with a SQLite database through a simple shortcode. It provides an easy way to retrieve and display data stored in the database.

## Installation
1. Download the plugin files.
2. Upload the `wirtzdata-plugin` folder to the `/wp-content/plugins/` directory.
3. Activate the plugin through the 'Plugins' menu in WordPress.

## Usage
To use the plugin, simply add the shortcode `[wirtzdata]` to any post or page where you want the data to be displayed.

## Configuration
Before using the plugin, ensure that the `.env` file is properly configured with the path to your CSV file collections. The CSV file should NOT be in a web accessible location. It will be read from the file system. The file should contain a line like this:

```
CSVFOLDER=/path/to/your/database.sqlite
```

## Uninstallation
When you uninstall the plugin, any options or data related to the plugin will be removed. Make sure to back up any important data before uninstalling.

## Support
For support, please open an issue on the plugin's GitHub repository.