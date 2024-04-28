# Product structure

The product structure for this site differs from a typical WooCommerce product structure. The reason being is that iResQ doesn't have typical "products".

Each device specific repair is it's own product, so each product is a combination of the device model and the repair type.

### Device Type

- The device type for each product is set under the default WooCommerce product category section (product_cat). These include

  - Phone
  - Tablet
  - Laptop
  - iPod
  - _Watch_ (these products aren't published yet)

### Repair type

- Repair types are under a custom taxonomy in the Products section (repairs).

### Brand

- Brands are set as global attributes.

### Models

- Models are also set as global attributes

All controllers for this project can be found in `/iresq-theme/app/Controllers`
