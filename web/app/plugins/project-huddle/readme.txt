=== ProjectHuddle Admin Site ===
Contributors: brainstormforce, 2winfactor
Donate link: https://projecthuddle.com
Tags: feedback, approval, design
Requires at least: 4.4
Tested up to: 6.0  
Stable tag: 4.5.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

ProjectHuddle Admin Site is a WordPress plugin that lets you get approval and feedback for all your design projects.

== Description ==

ProjectHuddle Admin Site is a WordPress plugin that lets you get approval and feedback for all your design projects.

== Installation ==
1. Upload the plugin files to the `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress

Check out the [Documentation](http://help.projecthuddle.com/) for detailed information!

== Changelog ==

= Version 4.5.1 - June 7th, 2022 =
- Improvement: Select status option UI changes.
- Fix: Comment position issue due to incorrect CSS classes.
- Fix: Resolved comment status not updating on the overview page.
- Fix: Resolved comment link from email is not redirecting to its related thread.

= Version 4.5.0 - May 30th, 2022 =
- New Feature: Added comment status options like Active, In-Progress, In-Review, etc.
- Improvement: Improved the error message if guest commenting is not allowed.
- Improvement: Added a PHP version check on the system check panel.
- Fix: Activity timeline pagination is not working on the initial page load.
- Fix: Approval "Term" is not working in website projects.
- Fix: Comment placement visibility issue on cross browsers like Mozilla.
- Fix: Comment position screenshot not working consistently.
- Fix: Conflicts with WPML Multilingual CMS plugin.
- Fix: Input field placeholder issue in mockup display settings.
- Fix: Overview page dropdown alignment issue.
- Fix: Plugin accessibility related issue fixes.
- Fix: Signup functionality not working.

= Version 4.4.2 - May 5th, 2022 =
- Fix: Automatic connection is not working.
- Fix: Private Comments option not visible on client site.
- Fix: Website connection UI issues in website metabox section.

= Version 4.4.1 - April 13th, 2022 =
- **Improvement** Added select and deselect all functionality to subscribers list when commenting.
- **Improvement** After page refresh, remember if the comment widget was minimized.
- **Improvement** Changed the style of the manual connection screen for better UX.
- **Improvement** Show the first comment attachment in the Overview popup.
- **Improvement** Toolbar position is saved after page refresh.
- **Fix** Comment position differs for logged-in & logged-out site visitors.
- **Fix** Duplicate navigation console error in mockup page.
- **Fix** Guest commenting is turned off and still allows new users to signup.
- **Fix** Fixed alignment issues on advanced tab of settings page.
- **Fix** Repeated error "rest_authentication_invalid_refresh_token_reuse" while verifying the user authentication token.
- **Fix** Show the error message only once for websites.

= Version **4.4.0** - March 22nd, 2022 =
- **New** Send reminder via email to thread assignee after X days of inactivity.
- **New** Send password reset link via email to the newly signed guest users.
- **Fix** Console error on mockups tab in the overview page.
- **Fix** Mutating props console errors on click of comment panel close icon.
- **Fix** Link to the Resolved comment email not redirecting to the related thread.
- **Fix** PHP warning while adding a new comment.
- **Fix** Saving PH cookies conflicting with WordPress login functionality.
- **Fix** Website URL instruction is added to the website setup page.

= Version **4.3.1** - March 8th, 2022 =
- **Improvement** Improved slack payload styling for all triggers messages.
- **Improvement** PDF Mockups / File Upload Addon plugins "Install" button position changed to extreme right.
- **Improvement** Saved settings notice added on the PH setting page.
- **Improvement** Trash button in the Image Card is highlighted in red.
- **Fix** After uploading different sizes of images constantly loading until we save/update that mockup.
- **Fix** Console error when clicking on the `@` button while commenting.
- **Fix** Console error while tagging user in the side drawer.
- **Fix** Comment point accuracy not working correctly on top of the images.
- **Fix** If a wrong file is uploaded, a proper error message is triggered on the mockup page.
- **Fix** Login through popup not working if double quotes used in the password.
- **Fix** Mockup overview close button issue.
- **Fix** Need to log in twice to add comments on a website while Force login option is enabled.
- **Fix** Progress bar "status" related console errors.
- **Fix** Project Administrator and Project Editor User access issue.
- **Fix** Tag option is not working on the overview page while editing comments from the sidebar.
- **Fix** Toggle button on the overview panel's mockup, activity, and website tab causes console error. 

= Version **4.3.0** - February 10th, 2022 =
- **New Feature** Added Slack integration with different actions support like a new comment added on a project, a project is approved/unapproved etc. [More Info](https://help.projecthuddle.com/article/153-integrate-projecthuddle-with-slack)
- **Improvement** Clock icon with tooltip added for the last updated column on tasks panel.

= Version **4.2.2** - February 7th, 2022 =
- **Improvement** Added Back button on the Login page.
- **Improvement** Added user role with the user name in the project members list.
- **Improvement** Copy to Clipboard button added in manual installation steps.
- **Fix** After deleting users, shows an error in the backend on the project members area.
- **Fix** Console error on Comment panel.
- **Fix** Console error on empty mockup view.
- **Fix** Console error on the login page.
- **Fix** If the website URL is empty, the next step should not be accessible (URL field is now required).
- **Fix** Prevent non-Latin characters while entering the username.
- **Fix** Search bar width issue on tasks page.
- **Fix** Signup popup "Cancel" button not working.
- **Fix** Tooltip is overlapping with the section border in the activity popup.
- **Fix** Login issue while opening a link in a new tab.
- **Fix** Login popup "Cancel" button not working.

= Version **4.2.1** - January 28th, 2022 =
- **Improvement** Compatibility with WordPress v5.9.
- **Fix** Login looping for users without existing refresh token.
- **Fix** Undefined function is_user_logged_in error.

= Version **4.2.0** - January 20th, 2022 =
- **New Feature** Website authentication system for Safari, Brave, Adblockers compatibility [More Info](https://projecthuddle.com/?p=46789)
- **New Feature** Private comments option for Mockup/Website Threads.
- **New Feature** Added a filter to view projects by a project member.
- **Improvement** Added loader to the autosave notice while creating a new website.
- **Improvement** Added responsive support to the Overview, mockup & website pages.
- **Improvement** Added tooltip for "Project" string on the overview page.
- **Improvement** Hide thread resolve option in case of the new thread.
- **Improvement** Project access links can be opened in a new window.
- **Improvement** Removed 'New Mockup' and 'New Website' admin menus.
- **Improvement** Website comment point accuracy.
- **Improvement** While adding comments, the red button text changed to "Cancel" instead of "Comment".
- **Fix** Activity off-canvas close button click action not working.
- **Fix** Console error while opening comment panel on website projects.
- **Fix** Console error while opening the Mockup.
- **Fix** In Mockup Display options, the default value of the image position dropdown is incorrect.
- **Fix** Member Tagging functionality not working.
- **Fix** Mockup panel close button not closing the sidebar on click.
- **Fix** Mockup display option background image and position issue.
- **Fix** Overview mockup and website tab redirecting to the empty screen with console error.
- **Fix** WordPress update alert notice is visible in the Diagnose even if WordPress is updated to the latest version. 

= Version **4.1.0** - December 1st, 2021 =
- **Improvement** Compatibility with PHP v8.0
- **Improvement** Improved auto save website notice UI.
- **Improvement** Improved login form UI.
- **Fix** Fixed Auto Draft title issue while adding new website.
- **Fix** Mockup close option does not work correctly.
- **Fix** Mockup title having special symbol showing the symbol code.
- **Fix** New empty mockup showing "Approved" status.

= Version **4.0.19** - April 14th, 2021 =
- **Fix** Issue where Admin users would get logged out if logging into a clients site under a different email.
- **Fix** Issue with Freemius beta testing not working.

= Version **4.0.18** - January 6th, 2021 =
- **Fix** Issue with pdf versions not being set for servers without GD installed.

= Version **4.0.17** - January 5th, 2021 =
- **Enhancement** Automatically update page urls when website url changes.
- **Fix** Manually updating page urls not saving.
- **Fix** Bug when dragging toolbar.

= Version **4.0.16** - December 30th, 2020 =
- **Fix** Missing "No activity" from translations on website panel.

= Version **4.0.15** - December 29th, 2020 =
- **Fix** Make sure login_with_access_token capability is removed from non-project client roles.

= Version **4.0.14** - December 28th, 2020 =
- **Fix** Error getting swallowed if deleting a task is unsuccessful.
- **Fix** Missing translations "No Activity" and "Before you do that..."

= Version **4.0.13** - December 11th, 2020 =
- **Fix** PHP developer warning in WordPress 5.6.

= Version **4.0.12** - November 10th, 2020 =
- **Fix** Issue with false error when sometimes sending manual report emails.

= Version **4.0.11** - November 3rd, 2020 =
- **Improvement** Ability to white-label a license from the dashboard. 
- **Improvement** Ability to upgrade licenses from the dashboard.
- **Improvement** Auto-Updates UI

= Version **4.0.10** - November 3rd, 2020 =
- **Fix** Issue with some security plugins blacklisting IP addresses.
- **Fix** Missing text domain in approval dialog.

= Version **4.0.9** - October 13th, 2020 =
- **Fix** Issue with dragging comments on mockups screen when zoomed in.
- **Fix** Issue where setup meta box can get accidentally hidden on new website pages.

= Version **4.0.8** - October 8th, 2020 =
- **Fix** Make new comment box click area larger.
- **Fix** "Something went wrong" message on system status page if phpmailer is not installed.

= Version **4.0.7** - October 6th, 2020 =
- **Fix** missing styles on website login page.

= Version **4.0.6** - October 1st, 2020 =
- **Fix** issue with subsequent images not loading if $post is overwritten by another plugin.

= Version **4.0.5** - October 1st, 2020 =
- **Enhancement** Add filters to force project approval. [Gist](https://gist.github.com/ajgagnon/2b809742b79b19896b200bc9ae00ce75)

= Version **4.0.4** - September 29th, 2020 =
- **Fix** Issue with media sometimes not loading.

= Version **4.0.3** - September 25th, 2020 =
- **Fix** Bug where logging in directly on website commenting modal need a refresh.

= Version **4.0.2** - September 25th, 2020 =
- **Fix** Issue where comments were empty on load if cookies are being blocked.
- **Fix** Password reset errors were sometimes getting swallowed and not returned to user.
- **Fix** Don't run sesssions in admin to prevent false positive site health error.

= Version **4.0.1** - August 22nd, 2020 =
- **New Feature:** Mockup UI Overhaul!
- **New Feature:** Front-end Mockups Versions Navigator.
- **New Feature:** Compare versions side-by-side with the current version.
- **New Feature:** Pinch/zoom on Mockups for desktop and mobile devices.
- **New Feature:** Adjust Mockup settings from the front-end.
- **New Feature:** Bulk change image display settings on Mockups.
- **New Feature:** Mockup Toolbar improvements.
- **New Feature:** Mockup navigation UI improvements.
- **New Feature:** Activity feed on Mockups.
- **New Feature:** Mockup project intro description.
- **Enchancement:** Faster, more performant PDF rendering
- **Update** Licensing SDK
- **Fix** Display box issues for 5.5

= Version **3.9.29** - August 14th, 2020 =
- **Fix** Freemius conflict with OceanWP theme.

= Version **3.9.28** - August 13th, 2020 =
- **Fix** Incorrect WordPress upgrade notice.

= Version **3.9.27** - August 13th, 2020 =
- **Fix** WordPress 5.5 compatibility.
- **Enhancement** Automatic Autoptimize configuration.

= Version **3.9.26** - July 28th, 2020 =
- **Enhancement** Make freemius theme + plugin tracking 100% OPT-IN, and stop sending this info for already migrated licenses.

= Version **3.9.25** - July 27th, 2020 =
- **Enhancement** Transition licensing to Freemius.

= Version **3.9.24** - June 30th, 2020 =
- **Fix** Issue where project clients weren't getting access links in the dashboard shortcode.

= Version **3.9.23** - June 18th, 2020 =
- **New Feature** Filter projects by approved or unapproved on All Websites or All Mockups page.
- **Fix** Issue with clients being added to other website projects if install code is being used and they visit another site while logged in.

= Version **3.9.22** - June 12th, 2020 =
- **Fix** Issue where daily emails weren't being sent if activity emails were disabled.

= Version **3.9.21** - May 28th, 2020 =
- **Fix** Broken javascript translations.

= Version **3.9.20** - May 21st, 2020 =
- **Fix** Website threads not loading on initial page visit for servers who couldn't get the referring url.

= Version **3.9.19** - April 28th, 2020 =
- **Fix** Issue with comment scroll not working for resolved comments.

= Version **3.9.18** - April 24th, 2020 =
- **Fix** Some missing javascript translation strings.
- **Fix** Issue with popup sometimes happening if new comment threads are saved quickly right after one another.
- **Fix** SVG icons in admin task editor styling.

= Version **3.9.17** - April 21th, 2020 =
- **Enhancement** No longer eager-load resolved tasks on website pages for better performance.
- **Fix** Issue where comment locations sometimes resolved to ProjectHuddle container during xpath.

= Version **3.9.16** - April 16th, 2020 =
- **Fix** Styling missing on file uploads addon in website commenting introduced in 3.9.15.

= Version **3.9.15** - April 16th, 2020 =
- **Fix** Fix large Chrome spacing issue with mockup comments introduced in 3.9.14.
- **Enhancement** Smaller css bundles for website commenting.

= Version **3.9.14** - April 14th, 2020 =
- **Fix** Latest Firefox stripping spaces in editor.

= Version **3.9.13** - April 9th, 2020 =
- **Enhancement** Website commenting perfomance improvement for pages with lots of comments.
- **Fix** Unnecessary PATCH request when viewing resolved website comments.

= Version **3.9.12** - April 6th, 2020 =
- **Fix** Issue with screen position comments being incorrect.
- **Fix** Issue with editing comments removing list items on some installs.

= Version **3.9.11** - April 1st, 2020 =
- **New Feature** Setup Wizard
- **New Feature** Self-diagnostic system status page.

= Version **3.9.10** - April 1st, 2020 =
- **Change** Include query strings in website urls by default for sites without permalinks enabled.
- **Enhancement** Add additional sorting and bulk updates to website threads listing for debugging purposes.

= Version **3.9.9** - March 31th, 2020 =
- **Fix** Issue with strange special latin characters in summary emails.

= Version **3.9.8** - March 27th, 2020 =
- **Fix** iframe sizing issues on Firefox browsers.

= Version **3.9.7** - March 25th, 2020 =
- **Improvement** Select default new task subscribers on project settings.

= Version **3.9.6** - March 24th, 2020 =
- **Fixed** Issue with mockup images not trashing on edit mockup pages.
- **Fixed** Issue with comments not resolving when new versions are added.
- **Fixed** Minor issue with late bailing on assignment emails if there's no recipient.

= Version **3.9.5** - March 20th, 2020 =
- **Fixed** Issue with upcoming FireFox version 75 and current developer edition.
- **Improvement** Add notice if WordFence author parameter is disabled in REST API.
- **Improvement** Close button also dismisses tutorial popup.

= Version **3.9.4** - March 19th, 2020 =
- **Fixed** Issue with access links not working with dashboard sites on WPEngine in Chrome browsers.
- **Fixed** Issue with larger logos getting squished in website toolbar/panel.

= Version **3.9.3** - March 13th, 2020 =
- **Improvement** Updated German translations.
- **Improvement** Added toolbar classes for easier styling.
- **Fixed** Issue with tutorial popup overflow.
- **Fixed** Added a timeout for when screenshots in firefox take too long on large pages.
- **Fixed** Fix session expired issue with hosts that have caching and do not allow authorization headers.

= Version **3.9.2** - March 11th, 2020 =
- **Fixed** Strange rounded corners antialiasing issue in Firefox.

= Version **3.9.1** - March 11th, 2020 =
- **Fixed** Logo stretching in website panel.

= Version **3.9.0** - March 11th, 2020 =
- **New Feature:** Website and webpage approvals!
- **New Feature:** New, brandable website toolbar UI.
- **New Feature:** Website notifications UI and "Undo" feature for delete and edit actions.
- **New Feature:** New activity view on website slide-out panel.
- **New Feature:** New comments view on website slide-out panel lets you see comments from all pages and filter them by assignee.
- **New Feature:** New pages view on website slide-out panel lets you sort by approval.
- **Improvement:** Improved commenting on "clickable" website elements (links, slider controls, tabs, will no longer execute when commenting)
- **Improvement:** Simplified logo settings panel.
- **Improvement:** Faster page comments loading - comments are pre-fetched before interface loads so they will appear much faster.
- **Improvement:** Better integration with SPAs (react, vue next, nuxt, etc.)

= Version **3.8.16** - February 25th, 2020 =
- Fix: Shortcode not loading on iOS Safari.

= Version **3.8.15** - February 19th, 2020 =
- Fix: Issue where users were getting "You've been added" emails when they add themselves to a project.

= Version **3.8.14** - February 12th, 2020 =
- Fix: Issue with ProjectHuddle comments appearing in comment rss feeds.
- Fix: Make disabled comments warning dismissable.

= Version **3.8.13** - February 11th, 2020 =
- Enhancement: Added inline licensing activation and expiration checker to plugins page.
- Fix: Access links sometimes not being sent in emails.

= Version **3.8.12** - February 4th, 2020 =
- Fix: Issue with session expired notice on mockup projects with samesite cookies in Chrome.

= Version **3.8.11** - January 26th, 2020 =
- Fix: Issue with nonce not refreshing during login for samesite cookies.

= Version **3.8.10** - January 21th, 2020 =
**IMPORTANT UPDATE!**
- Please update to this version to [fix issues with Chrome's Samesite cookies rules which going into effect in early February with Chrome 80](https://blog.chromium.org/2019/10/developers-get-ready-for-new.html).

= Version **3.8.9** - January 15th, 2020 =
- Fix: Issue with query variables including ph_access_token
- Fix: Issue with SPAs comments not refreshing.

= Version **3.8.8** - January 10th, 2020 =
- Fix: Issue with admin comment urls being parse wrong in Chrome browsers.

= Version **3.8.7** - December 27th, 2019 =
- Fix: Issue with access link being incorrect on mentions emails.

= Version **3.8.6** - December 5th, 2019 =
- Fix: Issue with estimated website comment locations sometimes extending beyond bottom of website for reporters with Edge browsers.

= Version **3.8.5** - December 5th, 2019 =
- Fix: Issue with emails not sending when members are added to new projects and activity emails are disabled.

= Version **3.8.4** - December 4th, 2019 =
- Fix: Javascript issue with project shortcode.
- Fix: Textdomain issue on "Your Daily Report".
- Update: Translations.
- Update: Better error handling for automatic connections.

= Version **3.8.3** - November 26st, 2019 =
- Fix: Issue with emails failing if user was deleted and not removed from project.

= Version **3.8.2** - November 22st, 2019 =
- Fix: Issue with some users getting 404 on access links for new installs.
- Fix: Issue with error reporting freezing for some users.
- Fix: PHP debug notice in 5.3 for register_meta.
- Fix: PHP debug notice for undefinted parent_post.
- Fix: Translation issue on register popup.

= Version **3.8.1** - November 21st, 2019 =
- Enhancement: Update translations and add French Translation
- Fix: Activity emails not being displayed on specific versions of outlook.
- Fix: File attachements not loading for non-logged in users if they are the first comment in a thread.

= Version **3.8.0** - November 20th, 2019 =
- [Update Post](https://projecthuddle.com/?p=32563)
- New Feature: Email summaries!
- New Feature: Daily and Weekly reports!
- New Feature: Assignment history!
- Enhancement: Integrated help modals in admin ui.
- Fix: Don't try to fetch tasks on website admin if not yet connected.
- Fix: Thread count not clearing when thread is deleted.

= Version **3.7.8** - November 20th, 2019 =
- Fix issue with 5.3 Big Image Threshold feature not displaying large mockup images at full size.

= Version **3.7.7** - November 13th, 2019 =
- Fix compatibility issue with WordPress 5.3.
- Don't try to fetch tasks if website project is not yet set up.
- Compatibility fix for custom avatar plugins.
- Revert document height calculation on website pages to prevent overflowing comments.

= Version **3.7.6** - October 23, 2019 =
- Change screenshot quality to prevent upload failures on some servers.
- Wrap screenshot in exception handling to prevent server failures for saving comment.
- Add screenshot php filters to disable or change quality.

= Version **3.7.5** - October 21, 2019 =
- Fix issue with mockup comment ordering changing after page refresh.

= Version **3.7.4** - October 16, 2019 =
- Fix website comment stickies extending beyond the document size from < v3.6.0 comments and below.
- Add notice for exclusions needed on Flywheel hosting.
- Fix php notice in permissions check.

= Version **3.7.3** - October 15, 2019 =
- Enhancement: Actions are automatically taken now after registrations
- Fix issue users comment email settings not being applied.
- Fix issue with not defaulting to access link login for new projects.

= Version **3.7.2** - October 3, 2019 =
- Fix issue with website comment email sometimes getting link stripped.

= Version **3.7.1** - October 3, 2019 =
- Fix issue with website options box not saving on edit website page.
- Fix issue where screenshots didn't bail if disabled by filter or not available.

= Version **3.7.0** - October 1, 2019 =
- [Update Post](https://projecthuddle.com/?p=29949)
- New task and project management interface!
- New @mentions feature!
- New Bulk editing features!
- Added filters to disable or change quality of screenshots.
- Fixed issue with comment locations failing if css was invalid.
- Fixed issue with query strings filter being overwritten on website commenting.

= Version **3.6.24** - September 25, 2019 =
- Fix regression with uninstall script not completely removing data.
- Exclude post types from Yoast SEO Sitemap (even though they should be hidden from search).
- Disable admin emails by default.

= Version **3.6.23** - September 23, 2019 =
- HostGator: Fix issue with logged in cookies not being set on internal REST requests on some servers.

= Version **3.6.22** - September 23, 2019 =
- Update system status file checks.
- Change license field to password type.
- Remove unused files.

= Version **3.6.21** - September 16, 2019 =
- Fix issue if full size dimensions are not created on some servers for large images.
- Fix firefox bug with panel visibility when hidden.
- Updated German translation.

= Version **3.6.20** - September 11, 2019 =
- Fix missing access link regression with child plugin.

= Version **3.6.19** - September 11, 2019 =
- Restrict Safari fix to only detected Safari browsers.

= Version **3.6.18** - September 11, 2019 =
- Fix issue with page comment count not updating when threads are deleted.
- Fix headers already sent issue when debug mode is on and website script is loaded.

= Version **3.6.17** - September 10, 2019 =
- Allow access links to load comment interface with Client Site plugin (requires Client Site plugin version 1.0.9)
- Fixed issue with shortcode returning all projects when user is not subscribed to anything.
- Fix issue with Safari not setting cookies if dashboard site hasn't been previously visited by user.

= Version **3.6.16** - September 9, 2019 =
- Added German translation!
- Check for caching and provide dismissable notice for exclusions.
- Fix compatibility with servers not setting referrer.
- Fix issue with comments loading on Elementor edit screen if dashboard site commenting is enabled.
- Fix PHP notices with undefined variables.

= Version **3.6.15** - September 3, 2019 =
- Fix pagination regression for dashboard and shortcodes.

= Version **3.6.14** - September 3, 2019 =
- Fix: multisite permalink flushing issue.

= Version **3.6.13** - September 3, 2019 =
- Fix: Permalink flushing issue causing 404 errors on website task/thread links.

= Version **3.6.12** - August 28, 2019 =
- New Feature! Added a tutorial popup for new users on Website projects.

= Version **3.6.11** - August 28, 2019 =
- Fixed issue with generating API keys for WordPress installations in subfolders.
- Fixed style issue with generating API keys.

= Version **3.6.10** - August 23, 2019 =
- Fixed translation issues in comment panels.

= Version **3.6.9** - August 22, 2019 =
- Fixed issue with module import causing larger bundle size on website comments.

= Version **3.6.8** - August 19, 2019 =
- Fixed issue with login screen not showing on access link for non-WordPress sites.

= Version **3.6.7** - August 19, 2019 =
- Fixed issue with session expired notice showing for some users.

= Version **3.6.6** - August 17, 2019 =
- Fixed issue with session expired notice showing for some users.

= Version **3.6.5** - August 14, 2019 =
- Fixed issue with guest commenting sometimes asking for password.

= Version **3.6.4** - August 14, 2019 =
- HotFix: Fix feature regression for filter that lets you specify default thread members.

= Version **3.6.3** - August 14, 2019 =
- HotFix: Issue with not being able to update older website projects to new method since in last version.

= Version **3.6.2** - August 14, 2019 =
- Fix: Issues with older versions of PHP and Strict Mode issues.
- Fix: Issue with automatic connection method showing generic popup instead of error message on some servers (flywheel).

= Version **3.6.1** - August 13, 2019 =
- Enhancement: Add filter to change default thread subscribers.
- Enhancement: Add help widget on new website interface for admin users.
- Fix: Edge-case issue with email suppression list.
- Fix: Validate new website form before submitting.

= Version **3.6.0** - August 13, 2019 =
- New! Client Site Plugin - Seamlessly connect your client's WordPress site with the child plugin [Learn More](https://projecthuddle.com/?p=26354)
- New! Guest commenting no longer requires a valid access token if project is set to "public".
- Enhancement - REST API 100 post limit removed for pages with more than 100 comments.
- Fix - Issue with WooCommerce changing logged out nonce

= Version **3.5.2** - July 23, 2019 =
- Fix invalid parameter(s) members error happening for some installations where the author id is not included in REST API.

= Version **3.5.1** - July 19, 2019 =
- Fix issue with Flywheel's caching and sessions.

= Version **3.5.0** - July 17, 2019 =
- Automatic Website Screenshots - A screenshot approximation of the user is now attached to all new comments.
- Manually change conversation subscriptions - You can manually add/remove users to conversations to keep someone in the loop and/or ensure relevant comment notifications.
- Markdown Shortcuts - A brand new editor lets you write markdown which gets automatically converted to WYSIWYG content on the fly!
- Fix issue with Flywheel's updated varnish caching rules
- Fix Copy/Paste issues on website comments.
- Read more here: https://projecthuddle.com/3-5-0-release/

= Version **3.4.8** - July 12, 2019 =
- Fix issue with website login errors not always showing.
- Fix issue with mockup admin comment panel not opening.
- Fix false positive PUT/PATCH/DELETE check in system status file.
- Add redirects check to system status file

= Version **3.4.7** - June 27, 2019 =
- Fix issue with last image approval not triggering project email.
- Compatibility fix for WordPress 4.7 and lower.

= Version **3.4.6** - June 20, 2019 =
- Fix issue with comment locations not working with empty class attribute on website projects.

= Version **3.4.5** - June 20, 2019 =
- Enable Access Link Login by default on new projects.
- Fix freezing issue on Activity page if projects are permanently deleted.
- Fix issue with icons not showing up on non-standard WordPress installations.

= Version **3.4.4** - June 19, 2019 =
- Fix conflict with Advanced Access Manager plugin.

= Version **3.4.3** - June 10, 2019 =
- Fix issue with error being thrown if wp-config.php cannot be located for REST API constant declaration.

= Version **3.4.2** - June 10, 2019 =
- Fix session manager conflict with Awesome Support plugin.

= Version **3.4.1** - June 10, 2019 =
- Fix issue with error being thrown if wp-config.php cannot be located for REST API constant declaration.

= Version **3.4.0** - June 10, 2019 =
- New Feature! Sketch Sync
- New Feature! REST API
- Fixed issue with pdf pages sometimes not loading.

= Version **3.3.3** - June 7, 2019 =
- Fixed issue with filter dashboard option not working.
- Fixed issue where mockup comments would sometimes get stuck to top of image with certain caching.
- Fixed issue where website comments count didn't update when resolved was toggled.
- Fixed issue with Manage button not showing on website comments panel.
- Fixed various debug notices.

= Version **3.3.2** - May 17, 2019 =
- Fixed issue with emails not being sent when users are added to project.

= Version **3.3.1** - May 15, 2019 =
- Fixed issue with javascript translations on shortcodes.

= Version **3.3.0** - May 13, 2019 =
- Passwordless commenting for clients!
- Assign Mockup Comments
- Improved Email notification rules
- Website commenting performance improvement

= Version **3.2.7** - May 10, 2019 =
- **Fix** Fix issue with thumbnails not loading when medium_large or full is not present.

= Version **3.2.6** - May 1, 2019 =
- **Fix** Additional translation issues.

= Version **3.2.5** - May 1, 2019 =
- **Fix** Website panel translation string issue.

= Version **3.2.4** - April 30, 2019 =
- **Fix** Issue with mockup images not deleting.
- **Fix** Issue shortcodes echoing css variables on some instances.

= Version **3.2.3** - April 16, 2019 =
- **Fix** Issue with ipad website comments not working on text nodes.
- **Enhancement** Add project link to shortcodes/dashboard.

= Version **3.2.2** - April 15, 2019 =
- **Fix** Issue with comments on new pages sometimes not saving the first time.

= Version **3.2.1** =
- **Fix** Issue with custom mockup defaults filter not working.
- **Enhancement** Updated Spanish translations.
- **Enhancement** Changed "Password" to "Create A Password" to avoid confusion on registration.
- **Miscellaneus** Cleaned up unused font directory.

= Version **3.2.0** =
- **New** PDF Support is here! Enable the addon for PDF Mockups.
- **New** Activity and overview dashboard.
- **New** Shortcodes have been completely reworked and are now more interactive.
- **New** Latest activity widget in projects.
- **Enhancement** API Permissions update to make way for loginless commenting.
- **Enhancement** Better license activation messages.
- **Enhancement** Edit Mockup Project Admin Refresh with more project details.

= Version **3.1.6** =
- **Fix** Issue with comment number not updating for mockup images on admin.

= Version **3.1.5** =
- **Fix** Issue with websites without trailing slash having comments not load on some pages.

= Version **3.1.4** =
- **Fix** Issue with image thumbnails not loading on mockup front-end until clicked.

= Version **3.1.3** =
- **Fix** Issue with mockup threads automatically resolving on new version.
- **Fix** Issue with mockups admin not loading on older WordPress versions.

= Version **3.1.2** =
- **Fix** Issue with website comments not loading on admin.

= Version **3.1.1** =
- **Enhancement** Spanish translation added!
- **Enhancement** Add media type field to image model.
- **Fix** Issue with login incorrectly causing an additional authentication check.
- **Fix** Issue with endpoint attribute actions not firing sometimes.
- **Fix** Issue with some missing translation strings.
- **Fix** Issue with special characters not rendering correctly in titles in some places.
- **Fix** Make sure images cannot be saved without featured media.
- **Fix** Make sure yoast metabox does not appear on our post types.

= Version **3.1.0** =
- **Enhancement** Huge website comment location accuracy update, including debug information for orphaned comments.
- **Fix** Fix WP Sessions library conflict with Awesome Support and Swift Security

= Version **3.0.31** =
- **Fix** Fix issue with access link not working on Flywheel hosting.

= Version **3.0.30** =
- **Fix** Fix issue with cross domain requests not working on subdomains.

= Version **3.0.29** =
- **Fix** Issue with Project Collaborators not being able to save project images.
- **Fix** Issue with not being able to drag unsaved website comment locations.

= Version **3.0.28** =
- **Fix** Issue with Project Collaborators not being able to save mockup projects.
- **Fix** Issue with website comments not attaching to inline SVGs.

= Version **3.0.27** =
- **Fix** Fix issue with not being able to login if "Allow non-users to self-register and make comment actions" is unchecked.

= Version **3.0.26** =
- **Fix** Issue with access links not working on WPEngine due to WP Session Manager upgrade.

= Version **3.0.25** =
- **Fix** Issue with xdomain and file uploads. https://github.com/jpillora/xdomain/pull/214

= Version **3.0.24** =
- **Improvement** Add headers check to system status file.
- **Improvement** Update xdomain.
- **Fix** Issue with file uploads extension not working if your site is SSL and your client's site is not.
- **Fix** Remove unnecessary logging.

= Version **3.0.23** =
- **Enhancement** Let users set password when registering.
- **Enhancement** Add WPEngine notice for cache exclusions.
- **Fix** Fix issue with project-level email suppressions not being honored in some cases.

= Version **3.0.22** =
- **Fix** Conflict with Ultimate Members plugin where it was force-closing comments on our post types.
- **Fix** No longer save page urls with trailing slash.

= Version **3.0.21** =
- **Fix** Actually fix date discrepacny on admin if users timezone is different from server.

= Version **3.0.20** =
- **Fix** Attempt to fix date discrepacny on admin if users timezone is different from server.

= Version **3.0.19** =
- **Fix** Date discrepacny on Safari browsers.
- **Enhancement** Update comment thread redirect to use new project functions.

= Version **3.0.18** =
- **Fix** Issue with members table not being created on multisite subsites.
- **Fix** Issue with collaborator role siloing not showing projects on all project pages.
- **Fix** Plugin activation link on addons page.
- **Fix** Issue with firefox dots shifting when hovered in latest version of Firefox.
- **Fix** Issue with iframe sizing on website comments not working on latest version of Firefox.

= Version **3.0.17** =
- **Improvement** Add PHP filter for default image options.
- **Fix** Vex reference error on website comments.
- **Fix** Remove autosize reference on share dialog.

= Version **3.0.16** =
- **Fix** Remove extra space at top of file to prevent headers already sent error.

= Version **3.0.15** =
- **Fix** Suppress session_start notices if other plugins or themes have already started sessions.

= Version **3.0.14** =
- **Improvement** Add opt-in error monitoring.
- **Improvement** Add ssl notice for sites not using ssl.
- **Improvement** Match website url regardless of trailing slash.
- **Improvement** Use loop instead of array_column to get member ids. Remove unused members table file.
- **Improvement** Don't redirect to default login page if password is incorrect.

= Version **3.0.13** =
- **Fix** Issue with some members not getting new comment emails.
- **Improvement** Updated help beacon.

= Version **3.0.12** =
- **Improvement** Use custom table to make project members more queryable.
- **Improvement** Tracking is disabled on hidden website comments to improve performance.
- **Improvement** You can now update website page urls from the admin dashboard.
- **Improvement** Improved database upgrade routine.
- **Fix** Issue with bulk approving images giving and "Invalid Nonce" error.
- **Fix** Issue non-html5 shortcode galleries not displaying properly.
- **Fix** Issue with shortcode galleries showing in wrong order.
- **Fix** PHP Notice on thread models with WP_DEBUG turned on.

= Version **3.0.11** =
- **Fix** Issue with users not being able to self-register on mockup projects.

= Version **3.0.10** =
- **Fix** Issue where project approval sends multiple emails.
- **Fix** Issue with not prompting user when they approve an image.
- **Fix** Assigning email missing comment content when assigned on initial creation.
- **Fix** Don't get the "added to project" email if you add yourself.
- **Fix** Don't get the you've been assigned email when you assign yourself.
- **Fix** Issue with the project_huddle shortcode not working with new functions.
- **Fix** Disable editing/deleting individual comment until it's saved to prevent duplicates.

= Version **3.0.9** =
- **Fix** issue with collaborate email sending when mockup project is saved.
- **Fix** issue with Mockup project approval setting on admin screen not applying.

= Version **3.0.8** =
- **Hotfix** issue with retina being enabled on mockup projects.

= Version **3.0.7** =
- **Fix** Issue with Next button disappearing on New Website page.
- **Fix** Fix issue with website page shortcode screenshot images displaying thumbnail size if custom size is set.
- **Improvement** Update rest update attribute filter to include project type.

= Version **3.0.6** =
- **Improvement** Links with incorrect access link are now redirected to login page instead of error message.
- **Improvement** Add additional confirmation to project access change to prevent accidental access changes.
- **Improvement** Additional actions added to login form.
- **Improvement** Additional functions added to get post meta.
- **Improvement** ph_query_project_subcollection automatically detects post type.

= Version **3.0.5** =
- **Improvement** Make database upgrade notice dismissable.
- **Fix** Issue with My Email Notifications meta box not updating on Website Projects.
- **Fix** Issue with Meta Box order not saving on other post types.
- **Fix** Issue with website project access resetting on websites when website url is updated.
- **Fix** Issue with database upgrade not applying to child sites on multisite installations.

= Version **3.0.4** =
- **Improvement** Addons page now shows available addons!

= Version **3.0.3** =
- **Fix:** Issue with relatively small mockup images not able to be added to mockup projects.
- **Fix:** Issue with website commenting changing heading sizes on load.
- **Fix:** Issue with subscribed project shortcode gallery format when no pages have been commented yet.
- **Fix:** Issue with Admin Bar disappearing if roles are customized.
- **Fix:** Issue with new member emails not sending for mockups.

= Version **3.0.2** =
- **Fix:** Force access link fallback in case project access options are not set during incomplete database upgrade.
- **Fix:** Issue with fastclick library causing text boxes not to focus on mobile mockup screens.

= Version **3.0.1** =
- **Fix:** Issue with image name not being correctly replaced via options.
- **Fix:** Issue with welcome email being sent on mockup visit.

= Version **3.0.0** =
- **BREAKING CHANGE** Refactored template files, structure and names to reuse between websites and mockups. PLEASE UPDATE YOUR CUSTOM TEMPLATES IN YOUR THEME.
- **Improvement** Mockup comments can now be resolved.
- **Improvement** Mockup comments now persist between versions.
- **Improvement** New mockup versions now have the option to resolve all comments when updating to new version.
- **Improvement** Comment conversation threads now paginate to increase initial loading speed.
- **Improvement** Mockup projects now require user accounts - users can self-register when comments are left.
- **Improvement** Website comments now show additional clicked html and clicked css selector debug information.
- **Improvement** Website comments reordering now persists between users.
- **Improvement** Comment resolve actions are now recorded in a conversation
- **Improvement** Mockups API updated to v2.
- **Improvement** Login/Registration on the front end doesn't require page reload.
- **Improvement** Improved logging for debug purposes
- **Improvement** Improved session handling using custom table to prevent wp_options leak.
- **Improvement** Website comments can now be viewed by hovering instead of clicking.
- **Improvement** Website comment drag and drop improved.
- **Improvement** Database upgrades are done async in the background.
- **Improvement** Updated default brand color.
- **Improvement** Website projects now display screenshots of pages in gallery format.
- **Improvement** Website install code updated to bust cache and work asynchronously.
- **Improvement** Updated versions functionality to better mirror WordPress revisions.
- **Improvement** Post type permalinks now correctly redirect in application.
- **Fix:** Issue with php 7.2 on posts controller showing notice
- **Fix:** Issue with special character in email subject lines, body.
- **Fix:** Issue with settings icon appearing on mockup pages when no settings dropdown should be shown.
- **Fix:** Missing comments issue with WPML CMS plugin.

= Version **2.7.5.1** =
- **Improvement** Add integrated help beacon.

= Version **2.7.5** =
- **Improvement** Website commenting now works with sites that use dynamic content loading with HTML5 history.
- **Improvement** Website comments widget is now draggable on mobile.
- **Improvement:** Add project id to project email filter.
- **Fix:** Fix issue with comment locations not updating when website url is changed
- **Fix:** Access token being stripped from add new user email link.

= Version **2.7.4.1** =
- **Fix:** Bust cache on style changes for comment widget location change.

= Version **2.7.4** =
- **New Feature:** Total progress for each website project is now shown on the ALL Websites page.
- **New Feature:** Change comment widget location on each website project.
- **Tweak** Background email processing only applies to ProjectHuddle emails now.
- **Fix:** Issue with website comment links not scrolling sometimes.
- **Fix:** Leave a comment tooltip not showing in Firefox.
- **Fix:** Issue with background email processing changing "from" name and address on WooCommerce emails.
- **Fix:** Issue with WordPress' Backbone JavaScript Client force deleting our post types.

= Version **2.7.3.1** =
- **Fix:** Fix issue with website comment clicks on chrome.

= Version **2.7.3** =
- **Enhancement:** Add email name and email address settings for ProjectHuddle Emails
- **Fix:** Fix issue with placeholder and cursor position on Firefox.
- **Fix:** Fix missing filters for email name and address from email class upgrade.

= Version **2.7.2.1** =
- **Fix:** Stop propagation of other click events when ProjectHuddle website click event happens (2nd try!).
- **Fix:** Fix syntax error with login form being outputted in website script when user is not logged in.
- **Fix:** Fix issue with turning off background email processing setting not working.

= Version **2.7.2** =
- **Enhancement:** Comment number appears in comment panel now to make it easier to see everything at a glance.
- **Enhancement:** More granular email control - users can update their email settings quickly and easily.
- **Enhancement:** Password reset functionality on website front-end.
- **Enhancement:** Background email processing to improve responsiveness for projects with many members.
- **Fix:** Issue with non UTF-8 characters in comments breaking website comment functionality.
- **Fix:** Stop propagation of other click events when ProjectHuddle website click event happens.
- **Fix:** Emails should now be translatable.
- **Fix:** Issue with set password email sometimes not sending for new users.

= Version **2.7.1.2** =
- **Fix:** Fix issue with new mockup comments not saving sometimes from last update.

= Version **2.7.1.1** =
- **Fix:** Allow logged in project members to access project even if access token wasn't generated

= Version **2.7.1** =
- **Enhancement:** Change comment number in website widget to match what's visible on screen.
- **Enhancement:** Made shortcode css and javascript conditional so they don't show up on pages unneccesarily.
- **Enhancement:** Removed "Hide Overflow" option from mockup images to prevent viewing issues on mobile devices.
- **Fix:** Issue with access token not working on servers that disable PHP Sessions (WPEngine)
- **Fix:** Centering issue for some mockup images that are incorrectly aligning left.
- **Fix:** Disable mockup submit button after sending to prevent duplicate comments.
- **Fix:** Issue with login form not showing for some website projects.

= Version **2.7.0.1** =
- **Hotfix:** Mockup comments dates converting to days.

= Version **2.7.0** =
- **New Feature:** User-self registration for websites. No need to create user accounts for clients, simply send an access link!
- **Improvement:** Copy website access link to clipboard via 1 click.
- **Fix:** Issue with more than 10 project members avatar not showing when assigned.
- **Fix:** Compatibility issues for aggressive template_include hooks called in themes and plugins
- **Fix:** Conflict with WooCommerce select2 library
- **Fix:** Issue where new project members added to websites were getting 2 welcome emails.
- **Fix:** Issue with firefox text formatting toolbar not appearing in correct spot.
- **Fix:** Issue with brand colors not properly showing in firefox for websites.

= Version **2.6.0.8** =
- **Fix:** Website comments incorrectly displaying on homepage for some users.

= Version **2.6.0.7** =
- **Improvement:** Emails are now sent individually to improve sending reliability on some servers.
- **Fix:** Missing translations for filtering on websites front-end
- **Fix:** Bug with Mockup comment dates being off in php 7.1

= Version **2.6.0.6** =
- **Fix:** Issue with not being able to remove project members for some users.
- **Fix:** Error message preventing system status page from loading.
- **Fix:** Issue with website comments not loading with avatars setting being turned off.

= Version **2.6.0.5** =
- **Fix:** Possible issue with avatars not being set in Safari and Firefox.
- **Fix:** Issue with client sites and main sites sharing same domain.

= Version **2.6.0.4** =
- **Fix:** Fix detached comments from previous issue with comments not saving on websites with trailing slash.

= Version **2.6.0.3** =
- **Fix:** Fix issue with trailing slash in website url causing comments not to work on new website projects.

= Version **2.6.0.2** =
- **Fix:** Firefox display issues.
- **Fix:** Changing website project domain name keeps comments in project
- **Fix:** Deleted/Trashed "This Website" website project won't output embed code on site

= Version **2.6.0.1** =
- **Fix:** Issue with mockup sharing not working.
- **Fix:** Issue with adding new members to projects on new installations.
- **Improvement:** PHP and WordPress version notices are now dismissible and won't prevent WordPress from loading.

= Version **2.6.0** =
- **Improvement:** BREAKING CHANGES! Complete website API rewrite. Please update and test any custom code before updating.
- **Improvement:** Beautiful & branded HTML email templates for all ProjectHuddle actions.
- **Improvement:** Easier drag and drop comments.
- **Improvement:** Websites Front end now has a show / hide toggle for resolved comments
- **Improvement:** Website pages can be renamed on the admin.
- **Improvement:** Website UI tweaks to make everything more natural, stop hiding UI elements.
- **Improvement:** Reply links now focus textarea on websites.
- **Improvement:** Other user’s website comments can no longer be edited by admin users
- **Fix:** Website widget no longer can get lost outside of the screen.
- **Fix:** Website comments have a slightly different layout and position better on fixed screen elements.

= Version **2.5.9.1** =
- **Fix:** Issue with custom button color being overwritten by plugin.
- **Fix:** Fix conflict with Media Cloud plugin using an unprefixed function.

= Version **2.5.9** =
- **Fix:** Use rest_url function to fix issues with subfolder installations.

= Version **2.5.8** =
- **Fix:** Fix issue with versions modal not loading.

= Version **2.5.7** =
- **Improvement:** Website commenting performance improvement. Website comment locations now load much faster.
- **Improvement:** Switched from custom postmessage to xdomain to increase website comment save speed.
- **Fix:** Fix issue image display options resetting on mockups.
- **Fix:** Fix issue with only headings showing on shortcodes when user is subscribed to mockups and websites.

= Version **2.5.6** =
- **Fix:** Fix issue with website comments not showing in backend due to typo in file url.

= Version **2.5.5** =
- **Fix:** Fix issue with comment not saving when mockup image is navigated before save.

= Version **2.5.4** =
- **Fix:** Fix issue with PHP versions less than 5.6.

= Version **2.5.3** =
- **Fix:** Fix shortcodes issue when mockups are disabled but websites are enabled.
- **Fix:** Fix missing no-follow links on some post types.

= Version **2.5.2** =
- **Fix:** Permission issues for non-admin users creating new mockups and websites with menu layout.

= Version **2.5.1** =
- **Fix:** Fixed headers already send message happening for some users.

= Version **2.5.0** =
- **New Feature:** New website comment front-end interface!
- **New Feature:** Display your logo on the website front-end panel.
- **New Feature:** Filter comments by assignments on the website front-end.
- **New Feature:** Assign users from the website front-end.
- **New Feature:** View debug information from the website front-end.
- **New Feature:** Trash website threads from the front-end.
- **New Feature:** Copy share link on the website front-end.
- **New Feature:** Filter comments by assignments on the website front-end.
- **New Feature:** See page progress bars on the website page panel.
- **Improvement:** Website interface now longer uses wp_head to prevent theme conflicts.
- **Improvement:** Website interface now uses system fonts to lighten asset loading.
- **Improvement:** Really Simple SSL compatibility notice.
- **Improvement:** More granular permissions for website actions (assigning, resolving, trashing threads).
- **Fix:** Issue with user not being notified sometimes when they are assigned an issue.
- **Fix:** Issue with new comment submit box not focusing on Safari.
- **Fix:** Issue with php notices when WP_DEBUG is turned on.

= Version **2.4.6** =
- **Fix:** Issue with project clients not being able to leave new comments.

= Version **2.4.5** =
- **Improvement:** Added basic comment dragging on websites.
- **Improvement:** Website performance improvement for scroll/window resize
- **Improvement:** Switched website comment panel layout to use flexbox for more flexible layouts.
- **Improvement:** Added additional filters to download link in dropdown.
- **Improvement:** Added additional template hooks in system status file.
- **Fix:** Fixed issue with comment locations not linking for some users
- **Fix:** Fixed issue with comments not saving for some Internet Explorer users
- **Fix:** Prevent negative comment location positions.

= Version **2.4.4** =
- **Improvement:** Performance improvements on website page with scrolling and window resizing.
- **Improvement:** Add automatic pagination to subscribed projects shortcode.
- **Fix:** Manually create embed code shortlinks to prevent conflicts with Jetpack shortlinks.

= Version **2.4.3** =
- **Fix:** Issue with icons not showing up due to cross domain restrictions

= Version **2.4.2** =
- **New Feature**: Option to approve entire project instead of just the current image.
- **Fix:** Issue with simplecheckbox.js having additional slash.
- **Fix:** Fixed typo on system status page.

= Version **2.4.1** =
- **Fix:** Added update script for new role changes that weren't applying.
- **Fix:** Issue with avatars being exceptionally large on project members meta box.

= Version **2.4.0** =
- **New Feature**: Role Restrictions. Project Clients and Project Collaborators now have their access restricted to only projects they are members of.
- **New Feature**: System status page to help with debugging.
- **Improvement:** The ProjectHuddle menu items are now all under one single menu.
- **Improvement:** Improved comment location matching on website pages.
- **Improvement:** Added indicator to the All Websites page to show which is the current site.
- **Improvement:** Better plugin compatibility with admin bar hiding on website widget.
- **Improvement:** Double-check that permalinks are enabled and show a notice if not.
- **Improvement:** Public methods for Mockups and Websites can now be accessed outside loop.
- **Improvement:** Authors are now shown on Mockup and Website projects.
- **Improvement:** Add filters to email functions for additional customization.
- **Improvement:** Additional postMessage origin security check for another layer of XSS protection.
- **Fix:** Issue with users getting emails about their own comments.
- **Fix:** Issue with trying to edit comment when page isn't reloaded.
- **Fix:** If mockup approvals and unapprovals are disabled, the button now does not display.
- **Fix:** Issue with website comments disappearing for plugins adding browser classes (Gravity Forms).
- **Fix:** Issue with Query monitor breaking website widget.
- **Fix:** Syntax error on all mockups page.

- **Fix:** Mockup and website permalinks now won't use prepended settings for users with non-standard permalinks.

= Version **2.3.0** =
- **New Feature**: Improved mobile support. [More Info](https://projecthuddle.com/mobile-improvements-image-zooming-approval-filters-and-more/)
- **New Feature**: Image zoom options. [More Info](https://projecthuddle.com/mobile-improvements-image-zooming-approval-filters-and-more/)
- **New Feature**: Filter images by approval on mockup pages. [More Info](https://projecthuddle.com/mobile-improvements-image-zooming-approval-filters-and-more/)
- **New Feature**: A better experience for users without accounts. [More Info](https://projecthuddle.com/mobile-improvements-image-zooming-approval-filters-and-more/)
- **Improvement:** Subscribed Project and ProjectHuddle shortcodes now include websites as an option.
- **Improvement:** Improved shortcode support on multisite with a new multisite attribute.
- **Improvement:** UI Improvements on mockup pages with image and comment panels opening/closing.
- **Fix:** Fixed issue with anonymous users line breaks and lists disappearing.
- **Fix:** Compatibility issue with Yoast Premium redirects module.

= Version **2.2.1** =
- **Fix**: Issue with mockup images not saving.

= Version **2.2.0** =
- **New Feature**: Option to approve all images on image approval mockup. [More Info](https://projecthuddle.com/projecthuddle-faster-artwork-approval/)
- **New Feature**: See mockup progress bars on all mockup and individual mockup pages. [More Info](https://projecthuddle.com/projecthuddle-faster-artwork-approval/)
- **Improvement**: Refactored approvals classes to be more useful.
- **Fix**: Fixed issue with download image link not appearing in dropdown.

= Version **2.1.2** =
- Security Issue: Fix Cross Server Scripting vulnerability.
- Fix issue with permalinks not flushing for some users
- Fix issue with deleting mockup thread showing confirmation box twice.
- Update template loading function to prevent possible conflict with theme template files
- Fixed php notices that were introduced in latest version.

= Version **2.1.1** =
- Fix issue with username showing slug instead of display name

= Version **2.1.0** =
- Complete rewrite of Mockups backend to use WP API. BREAKING CHANGES. Please update your plugins custom code to use new actions and filters.
- **New Feature**: Comment WYSIWYG editor. Highlight text to bold, italic, make lists, links and add code snippets.
- **New Feature**: Mockups now have front-end comment navigation.
- **New Feature**: Website customization colors.
- **New Feature**: Option to autohide bubbles after successful comment.
- **New Feature**: Option to hide resolved comment pins on websites.
- **Improvement**: Mockup comments now have updated UI, scrolling overflow.
- **Improvement**: Faster comment submission rendering.
- **Improvement**: Website embed code to be ssl agnostic to prevent ssl issues.
- **Improvement**: Select2 style and script compatibility issues on backend.
- **Improvement**: Prev/Next buttons and image tray now hidden if only one image exists.
- **Improvement**: Mockup images won't fade once already loaded.
- **Improvement**: Users without comment permissions won't see comment box on front-end.
- **Modification**: Changed /project/ url to /mockup/ for better compatibility.
- **Fix**: Email not sending when website comment is resolved.
- **Fix**: Highlight color not being applied to website toolbar and comments.
- **Fix**: project_huddle shortcode sometimes opening up in theme lightbox.
- **Fix**: Unapproved option not working on shortcode.
- **Fix**: Website comments not working on some subdomain installs.
- **Fix**: Error notice sometimes appearing on website login screen.
- **Fix**: Don't allow anonymous users to receive email notifications on websites yet.

= Version **2.0.3** =
- Roots framework compatibility fixes.
- Changed website toolbar z-index to maximum to prevent overlay of other items.
- Fixed an issue with backgrounds not transparent on website toolbar.
- Fixed an issue with email not being sent when a person is added to a website project.
- Fixed an issue with ssl forwarding and the login form.
- Fixed an issue with emails not being validated if they have a space at the end
- Fixed image scaling issue on mockup thumbnails in toolbar.

= Version **2.0.2** =
- Fixed issue with image insert not working if large thumbnail is not available
- Fixed compatibility issue with Avada framework and select2.

= Version **2.0.1** =
- Fixed issue with user roles not being able to move or delete threads.
- Fixed php notices.
- Added website threads untrash screen.
- Added option to turn off script shielding on mockup and website pages.
- Fixed issue where WooCommerce was redirecting all roles away from admin.
- Fixed select2 conflict with Yoast SEO.
- Fixed compatibility issues with shortcode by returning instead of echoing.
- Add additional actions and filters to shortcode output.
- Update EDD plugin updater class.
- Add ProjectHuddle capabilities to WordPress Editor role.
- Start storing image approval dates.
- Use smaller size for image thumbnails on Mockup admin to increase page performance.

= Version **2.0.0** =
- Huge new feature! Use ProjectHuddle to collect feedback on live websites! [Learn More](https://projecthuddle.com/website-collaboration-bug-tracking/)
- New feature! Custom user roles. Create special users for ProjectHuddle who can only edit and see ProjectHuddle projects, keeping your posts and pages separate.
- Prefixed hidden class to prevent conflicts with other overzealous themes and plugins.

= Version **1.3.1** =
- New option to disable "Unapprove" for non-logged in users.
- People now get an email when they are manually subscribed to a project.
- Fixed issue with users not auto-subscribing to projects when they approve.

= Version **1.3.0** =
- New [ph_subscribed_projects] shortcode! List all projects and approval status a user is subscribed to. [Learn More](http://docs.projecthuddle.com/article/116-adding-a-subscribed-projects-shortcode)
- Added the ability subscribe WordPress users or email address to projects in admin. [Learn More](http://docs.projecthuddle.com/article/117-how-to-add-or-remove-people-to-a-project)

= Version **1.2.2.1** =
- Translation hotfix - updated translation files to account for new features.
- Fixed double quote issue in default approval dialog template settings.

= Version **1.2.2** =
- New "Terms" clickwrap option for project approvals.
- New approval text templating in settings.

= Version **1.2.1** =
- Email notifications now include comment content.
- Added additional variables to ajax event actions.

= Version **1.2.0** =
- Fixed issue with images not centering with horizontal scrollbars.

= Version **1.1.9** =
- Updated for compatibility with WordPress 4.5 and Backbone 1.2.3.

**1.1.8**
- Added extensions tab to settings page.
- Added custom meta box control for extensions.

= Version **1.1.7** =
- Fixed js error on mobile devices not allowing projects to load.

= Version **1.1.6** =
- Fixed the issue with the top bar on duplicate id on top bar not displaying on edge browsers.

= Version **1.1.5** =
- Fixed issue with control bar dropdown on latest firefox.
- Fixed issue with dropdown menu in Internet Explorer.
- Compatibility for thumb drawer dropdown on browsers that don't support transforms.

= Version **1.1.4** =
- Fixed missing argument notice for when WP_DEBUG is enabled.
- Updated loading animation style.
- Changed font stack for more consistent UI across devices.
- Tweaked tooltip display.
- Fixed IonIcons files being loaded twice on front-end.
- Fixed issue with order not updating on front-end when comment thread is deleted.
- Comment locations now automatically trash when last comment is removed.

= Version **1.1.3** =
- Fixed issue with not being able to untrash project images and comment threads.

= Version **1.1.2** =
- Added download image link option to project options.
- Fixed issue WordPress 4.4 not allowing uppercase in url slugs.
- Fixed issue with default settings page colors not showing in admin on new installs.

= Version **1.1.1** =
- This version of ProjectHuddle is now ProjectHuddle Pro!
- Fixed issue with license handler throwing errors for some users.
- Added WP-JS-Hooks to allow extensions to hook into initialization of backbone elements.
- Added additional hooks and filters for better customization.

= Version **1.1.0** =
- Image versions! ProjectHuddle now keeps track of your all your revisions with versions. Always have a recorded history of all your comments and revisions, every step of the way.
- Added option to disable email sharing on each project.

= Version **1.0.1** =
- Translation ready! Added translation files.
- Fixed issue with license activation for auto updates.

= Version **1.0.0** =
- Initial release
