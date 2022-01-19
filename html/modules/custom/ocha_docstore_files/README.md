# Flysystem adapter for doc store

## Difficulties

- Use uuid to load files, not filename
- Store our uuid in managed_file, not auto generated one
- Add media entity that is able to handle *file revisions*

## Local settings

```php
// flysystem
$flysystem_schemes = [
  'docstore' => [ // The name of the stream wrapper.
    'driver' => 'ocha_docstore', // The plugin key.
    'config' => [
      'uri' => 'http://docstore.local.docksal',
      'api_key' => 'abcd',

      'root' => 'sites/default/files/docstore',
      'public' => TRUE, // In order for the public setting to work, the path must be relative to the root of the Drupal install.
      'name' => 'Documents store files', // Defaults to Flysystem: scheme.
      'description' => 'Documents store files',  // Defaults to Flysystem: scheme.
      'cache' => TRUE, // Cache filesystem metadata. Not necessary for the local driver.
      // 'replicate' => 'ftpexample', // 'replicate' writes to both filesystems, but reads from this one. Functions as a backup.
      'serve_js' => FALSE, // Serve Javascript or CSS via this stream wrapper.
      'serve_css' => FALSE, // This is useful for adapters that function as CDNs like the S3 adapter.
    ],
  ],
];

// Don't forget this!
$settings['flysystem'] = $flysystem_schemes;
```
