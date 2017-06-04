# Recommended Filters
These are filters that are recommended for excluding internal traffic and spambots from Google Analytics reports.

## Exclude My IP
Exclude the IP addresses of admins and developers.
Filter Type: Predefined
Exclude, traffic from the IP addresses
[Find your IP address here](https://www.google.com/#q=my+ip)

## Valid Hostnames
Only allow traffic to valid hostnames. [Generate a valid hostname filter pattern here](https://gearside.com/nebula/utilities/domain-regex-generator/?utm_campaign=documentation&utm_medium=readme&utm_source=ga+filters#customhostnames)
Filter Type: Custom
Include, Hostname
Ex: `\.*gearside.com|\.*gearsidecreative.com|\.*googleusercontent\.com`

## Spambot Language
Exclude spambots that modify their language attributes.
Filter Type: Custom
Exclude, Language Settings
`.{15,}|\s[^\s]*\s|\.|,|\!|\/`

## Security Precautions
Exclude bot traffic warnings (detected by Nebula) from affecting reporting. These will still show up on the unfiltered view.
Filter Type: Custom
Exclude, Event Category
`Security Precaution`

# Other Filters
These are filters that may be useful in certain cases, but aren't applicable to all websites.

## Lowercase URIs
Converting URIs to lowercase prevents a page's data from being split if a user enters a URL with capital letters.
*Note: This is now handled by Google Autotrack in Nebula.*
Filter Type: Custom
Lowercase, Filter Field: Request URI