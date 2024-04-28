### Launch day checklist

#

1. Create a new branch in bitbucket for the host changes

   1. Go into the `/trellis/group_vars/production` directory and open `wordpress_sites.yml`
   2. Change the `site_hosts` value to this
      ```
      site_hosts:
        - canonical: www.iresq.com
      redirects:
        - iresq.com
        # any other subdomains
      ```
      - **For staging** do the same thing to `/trellis/group_vars/staging/wordpress_sites.yml` but change the site_host to `iresq.makedigital.dev`
   3. Next, run the command from the `/trellis` directory
      ```
      $ ansible-vault edit group_vars/production/vault.yml
      ```
      1. Change the value of `filemaker_url`, `filemaker_uname`, `filemaker_pw` to the production Filemaker server (should be in keeper or given to you by iResQ's Filemaker rep)
      2. Hit the `esc` key and then type `:wq` and hit `enter`

2. Merge this branch with `dev` and `master` and deploy the changes.

   1. While that's deploying, turn on CMP plugin on the production site to display the coming soon page to anyone except for admins.
   2. Now head over to kinsta and add the `iresq.com` domain.
   3. Go to the DNS and change the root record to the new sites IP, `34.70.95.137` (this can also be found on the kinsta dashboard)

3. Once the deploy has finished, go to the **tools** tab in Kinsta and click **Search and replace**. Replace `//iresq.makedigital.dev` with `//iresq.com`.
4. Once the site has propogated and you are now seeing the coming soon page when visiting `iresq.com`, it's time to start testing orders.

   _You can keep the sandbox authorize.net credentials in the WooCommerce settings for this so you don't have to use a real payment method._

   1. Test a handful orders. Anything from a single free diagnosis to a cart with 10 items.
   2. Check the order details page and make sure the order numbers and item numbers look correct and that no **filemaker error** is displaying on the page.
   3. Check with the client and ask if the order came through correctly to FileMaker
   4. Now check with the client to make sure the order created the correct shipment based on the shipping selected at checkout.

      _Be very thorough in this section, the last thing we want is for orders to not process correctly after the site has been made open to the public_

5. Now switch the all plugins to the production setting if you haven't already done so
   - Authorize.net
   - Fedex for WooCommerce
   - USPS
   - Any SMTP plugins
