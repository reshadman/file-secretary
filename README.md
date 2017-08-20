# Laravel File Secretary
This package handles anything related to files, *Cached Resizable Images*, *Database tracked files*, *Static assets caching*, *Private Files*.

## Usecases
 - **File Center**: A Simple Eloquent Model, Which allows you to track your files, If you are not interested you can ignore it.
 - **Resizble Images the right way**: Based on intervention, fully configurable. Files are generated on the fly and are cached without the participation of PHP once generated even if you use a cloud file service provider like Rackspace. You can use your own templates for generating images based on your needs or you can use the dynamic template provided by the package. 
 - **Private files**: You can have private files that are served only if the criteria is met.
 - **Versioned static assets**: Your task runner generated some static assets, but they are still served through your main app (or you have set another domain for them), but you still can't use the features of your CDN servive provider (Like Rackspace), This package allows you to upload your entire assets to your cloud CDN, they are versioned automatically and you can address them with a simple function call in your templates.
