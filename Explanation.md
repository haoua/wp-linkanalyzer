# Plugin Explanation

## Problem to be solved
The problem we are trying to solve here is to provide a tool to help administrators improve their SEO rankings. To do so, they'll need to know how the page of their websites are linked to the homepage.

## Technical Specification
###Overview
WP LinkAnalyzer is a Wordpress plugin helping administrators to analyze their website's structure to improve their SEO.

### How It Works

1. **Crawling**: When the admin triggers a crawl on the admin page, the plugin performs the following steps:
	- Deletes results from the last crawl from cache.
	- Deletes the existing `sitemap.html` and `homepage.html` files.
	- Extracts internal hyperlinks from the homepage.
	- Stores the results in the database.
	- Saves the homepage as an HTML file on the server.
	- Creates a sitemap.html.

2. **Result Display**: When the admin requests to view the results, the plugin retrieves data from storage and displays it on the admin page.
	- Data are either retrieves from cache or from database depending if cache is available.

### Technical Decisions

- **Database Storage**: I chose to store results in a WordPress database to maintain data integrity and provide efficient retrieval. Needed tables are created when plugin is activated and are dropped when uninstalled.

- **HTML Sitemap**: The sitemap is generated in HTML for its simplicity.

- **User-Friendly**: The plugin's interface is simple to make it easier for user to focus on the data.


## Achieving the Desired Outcome

WP LinkAnalyzer effectively addresses the admin's desired outcome as per the user story by providing a user-friendly tool to:

- Analyze internal link structure.
- Create a visual sitemap.

## Potential Future Improvements
While the WP LinkAnalyzer plugin accomplishes its primary objectives, there is always room for enhancement and further development. Although certain features and optimizations were not implemented in this version due to time constraints, I've identified potential areas for improvement:
### Cron task
I haven't had time to go into this aspect of the project. By asking around, I learned that WordPress cron jobs depend on visitors to be launched. On a low-traffic site, this could pose a problem for administrators. However, I do realize that running a cron job in the "classic" way could also cause problems depending on the server settings.

### Crawl standalone task
To avoid blocking the user, it would be interesting to run the crawl in the background. This would allow the user to leave the page even if the crawl is not finished. It also adds the possibility of displaying a loader.

### Asynchronous data retrieval
Although on a fairly basic site data retrieval is quite fast, it would be interesting to run it asynchronously to improve the user experience.

### Customization Options
- Offering configuration settings to adjust crawl intervals, url to analyze or max depths can provide administrators with more control.

### Performance Optimization
- Continuously improving the plugin's performance to handle larger websites and data sets efficiently.

### Implementing test
- Implementing test would really be interesting for future development


Feel free to reach out if you have any further questions or need additional information.
