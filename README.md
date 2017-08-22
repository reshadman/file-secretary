# Laravel File Secretary
This package handles anything related to files, *Cached Resizable Images*, *Database tracked files*, *Static assets caching*, *Private Files*.

## Usecases
 - **File Center**: A Simple Eloquent Model, Which allows you to track your files, If you are not interested you can ignore it.
 - **Resizble Images the right way**: Based on intervention, fully configurable. Files are generated on the fly and are cached without the participation of PHP once generated even if you use a cloud file service provider like Rackspace. You can use your own templates for generating images based on your needs or you can use the dynamic template provided by the package. 
 - **Private files**: You can have private files that are served only if the criteria is met.
 - **Versioned static assets**: Your task runner generated some static assets, but they are still served through your main app (or you have set another domain for them), but you still can't use the features of your CDN servive provider (Like Rackspace), This package allows you to upload your entire assets to your cloud CDN, they are versioned automatically and you can address them with a simple function call in your templates.

## Adding Contexts

**Context have following attributes:**

 - Context name: Which is appeared in database.
 - Context disk driver: Which is used to manage the file. 
 - Context privacy: Which gets a callback class on file request. You can write your own callback handlers.
 If the privacy check is not successful the file
 would not be downloaded. Otherwise it will be downloaded.
 - Context path: Which maps the context name to the prepend of the files, allows to easily use same
 drivers for multiple contexts.
 - Driver base address: When set, Instead of your sites generated url, this url will prepended to the
 address.
 - Context Category: Which has tree type: (`basic_file`, `image`, `asset`).
 
 
 > A not on images: If you set the driver_based address for the file, it is used for
 generated template images too, to disable this you can set the `ignore_base_address_for_templates` to the context.
 
 > If a context is private but a base_address is set, it will use the base_address and no checks will be applied.
 
 