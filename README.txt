EcoLoop - Pickups, vehicles and drivers (PHP + MySQL, no framework)

INSTALL (XAMPP / WAMP / MAMP)
1. Copy this folder into htdocs (XAMPP) or www (WAMP). Start Apache and MySQL.
2. phpMyAdmin > Import > database.sql   (creates the "ecoloop" database; it resets the tables)
3. Check config.php (MySQL host, user, password).
4. Create the demo accounts ONCE, then delete the script:
      browser : http://localhost/ecoloop_php/install_users.php
      or CLI  : php install_users.php
5. Open http://localhost/ecoloop_php/ and log in.

DEMO ACCOUNTS (password for all: Password123!  -> change it in Admin > Users)
  admin    admin@ecoloop.test    full back office
  driver   hichem@ecoloop.test   Hichem Mansouri (Truck T-14 in demo data)
  driver   nour@ecoloop.test     Nour Gharbi
  driver   amine@ecoloop.test    Amine Jlassi
  citizen  amira@ecoloop.test    Amira B. (requests and follows pickups)

THE DRIVER USER
- An admin creates a driver in Admin > Users > "Create a driver account" (name, email, password).
  This adds the person to the collectors list AND creates the login linked to it.
- The admin assigns pickups + a vehicle in Admin > Collections.
- The driver logs in and lands on "My route" (driver/index.php):
    * sees only his own pickups: Today / Overdue / Upcoming, with address, note, materials, vehicle
    * "Start: I'm on the way" (Scheduled -> On the way): needs a vehicle and the pickup day to be today or earlier
    * "Mark as collected" (On the way -> Completed)
- A driver with active pickups cannot be deactivated until they are reassigned.

FILES
config.php, database.sql, install_users.php
login.php, logout.php            authentication (password_hash, session_regenerate_id, CSRF, 5 tries / 5 min)
index.php, request.php           citizen: My Collections, request a pickup
driver/index.php                 driver: my route
admin/collections.php            admin: assign collector + vehicle, change status
admin/vehicles.php               admin: fleet
admin/users.php                  admin: create drivers, activate/deactivate, reset passwords
includes/                        db.php, helpers.php, auth.php, header.php, footer.php
assets/style.css

RULES
- Roles: citizen, driver, admin. Each page checks the role (require_login).
- A vehicle in maintenance cannot be assigned; one vehicle cannot have two active pickups at the same date and slot.
- Prepared statements (PDO), HTML escaping (e()), CSRF token on every POST.
- Before going online: change all passwords, delete install_users.php, use HTTPS.
