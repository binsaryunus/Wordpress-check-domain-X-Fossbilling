<section class="plugin-description">
  <h1>Fossbilling Domain Search <small>v1.3</small></h1>
  <p>
    <strong>Fossbilling Domain Search</strong> adalah plugin WordPress ringan yang memungkinkan pengunjung mencari ketersediaan nama domain di semua TLD yang dikonfigurasi di Fossbilling. Plugin ini menampilkan daftar TLD, melakukan pengecekan via API, menampilkan harga pendaftaran, dan langsung menambahkan domain ke keranjang belanja.
  </p>

  <h2>Key Features</h2>
  <ul>
    <li><strong>Dynamic TLD Fetching:</strong> Menarik daftar TLD aktif dari Fossbilling Guest API (<code>/tlds?allow_register=true</code>).</li>
    <li><strong>Realtime Availability Check:</strong> Mengecek ketersediaan domain satu per satu via <code>/check</code>.</li>
    <li><strong>Inline Pricing Display:</strong> Menampilkan <code>price_registration</code> dari respons TLD.</li>
    <li><strong>Add-to-Cart Integration:</strong> Tombol “Add to Cart” memanggil <code>/cart/add_item</code> dengan payload:
      <pre>{
  "id": PRODUCT_ID,
  "period": 1,
  "quantity": 1,
  "config": {
    "domain": "namadomain.com",
    "action": "register"
  }
}</pre>
      dan redirect otomatis ke halaman keranjang.
    </li>
    <li><strong>Admin Settings Page:</strong>
      <ul>
        <li><em>API Base URL</em> (Fossbilling Guest API)</li>
        <li><em>Product ID</em> domain (dari Catalog → Products)</li>
        <li><em>Default Checkout URL</em> (halaman cart)</li>
        <li><em>Shortcode Tag</em></li>
        <li><em>Placeholder Text</em></li>
      </ul>
    </li>
    <li><strong>AJAX-Powered, Non-Blocking UI:</strong> Form responsif tanpa reload halaman.</li>
    <li><strong>Security-First:</strong> Nonce verification, sanitasi input, regex validation, dan escaping output.</li>
    <li><strong>Lightweight & Performant:</strong> Hanya bergantung pada core WordPress, tanpa library eksternal.</li>
    <li><strong>Easy Styling:</strong> Tabel hasil dan tombol styled dengan CSS bawaan, bisa di-override melalui Custom CSS.</li>
    <li><strong>Multiple Instances:</strong> Dukung beberapa shortcode di satu halaman.</li>
  </ul>

  <h2>Changelog</h2>
  <dl>
    <dt>Version 1.3</dt>
    <dd>
      <ul>
        <li>Introduced default checkout URL placeholder <code>{domain}</code> support for flexible cart links.</li>
        <li>Enhanced “Add to Cart” logic to call Fossbilling <code>guest/cart/add_item</code> via AJAX with <code>config.action = "register"</code>.</li>
        <li>Improved button styling and text (“Add to Cart”).</li>
      </ul>
    </dd>
    <dt>Version 1.2</dt>
    <dd>
      <ul>
        <li>Enabled custom shortcode tag configuration.</li>
        <li>Improved admin settings for layout and custom CSS.</li>
      </ul>
    </dd>
    <dt>Version 1.1</dt>
    <dd>
      <ul>
        <li>Added “Add to Cart” via Guest API integration.</li>
        <li>Redirect to checkout after add-to-cart.</li>
        <li>Security and validation refinements.</li>
      </ul>
    </dd>
    <dt>Version 1.0</dt>
    <dd>
      <ul>
        <li>Initial release: fetch TLDs (<code>/tlds</code>), check availability (<code>/check</code>), display pricing.</li>
        <li>Basic AJAX form and results table.</li>
      </ul>
    </dd>
  </dl>

  <h2>Usage</h2>
  <ol>
    <li>Activate plugin, lalu buka <em>Settings → Domain Search</em> untuk konfigurasi:
      <ul>
        <li><strong>API Base URL:</strong> <code>https://billing.example.com/api/guest/servicedomain</code></li>
        <li><strong>Product ID:</strong> cek di Fossbilling Admin → Catalog → Products → edit → parameter <code>id=…</code></li>
        <li><strong>Default Checkout URL:</strong> misalnya <code>https://billing.example.com/cart?domain={domain}</code></li>
        <li><strong>Shortcode Tag</strong> &amp; <strong>Placeholder</strong></li>
      </ul>
    </li>
    <li>Tambahkan shortcode di halaman/post:
      <pre>[bb_domain_search]</pre>
      atau override sekali:
      <pre>[custom_search checkout_url="https://billing.example.com/cart?domain={domain}"]</pre>
    </li>
    <li>Pengunjung masukkan nama domain, klik <em>Check</em>, lalu klik <em>Add to Cart</em> untuk domain tersedia.</li>
  </ol>
</section>
