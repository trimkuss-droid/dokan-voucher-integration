# Dokan Voucher Integration

Integruoja WooCommerce PDF Vouchers su Dokan - leidžia parduotuvėms patvirtinti dovanų kuponus.

## Funkcionalumas

✅ **Voucher validavimas** - Patikrina kupono kodą pagal WooCommerce PDF Vouchers duomenis  
✅ **Sumos validavimas** - Saugoja, kad kupono suma neviršytų užsakymo sumos  
✅ **Vienkartinis naudojimas** - Kuponas gali būti panaudotas tik vieną kartą  
✅ **Automatinis statusas** - Orderis automatiškai keičiamas į "completed"  
✅ **Dashboard integracijos** - Voucher kodo įvedimo forma tiesiai Dokan dashboard'e  
✅ **Orders filtravimas** - Dokan rodo tik užbaigtus ordarius (completed)  
✅ **Loginai** - Visos kupono panaudojimo operacijos saugomos duomenų bazėje  

## Instaliacijos Žingsniai

### 1. Failų iškėlimas

- Atsisiųskite `dokan-voucher-integration.zip`
- Išpakuokite į `/wp-content/plugins/dokan-voucher-integration`

Arba per Hostinger File Manager:
1. Eikite į `wp-content/plugins/`
2. Sukurkite naują aplanką: `dokan-voucher-integration`
3. Iškėlinkite visus failus

### 2. Plugino aktivavimas

1. WordPress Admin → Plugins
2. Raskite "Dokan Voucher Integration"
3. Spustelėkite "Activate"

### 3. Reikalingi pluginai

Įsitikinkite, jog yra įdiegti:
- ✅ Dokan (3.0+)
- ✅ WooCommerce (4.0+)
- ✅ WooCommerce PDF Product Vouchers

## Naudojimas

### Vendor Dashboard

1. Prisijunkite kaip parduotuvės savininkas (vendor)
2. Eikite į Dashboard
3. Raskite "Patvirtinti dovanų kuponą" skiltį
4. Įveskite:
   - **Kupono kodą** - iš dovanų kupono
   - **Užsakymo ID** - kuriam kuponui priskirti
5. Spustelėkite "Patvirtinti kuponą"

### Logika

1. Kuponas validuojamas pagal WooCommerce PDF Vouchers
2. Patikrinama ar kuponas jau panaudotas
3. Patikrinama kupono suma / tipas
4. Jei viskas OK:
   - Orderis keičiamas į "completed" statusą
   - Loginai saugomi duomenų bazėje
   - Rodoma sėkmės žinutė

### Orders Rodinyje

- Dokan dashboard rodo **tik "completed" ordarius**
- Vygdomi ordarii automatiškai paslėpi
- Vendor'iai mato tik baigtas (apmokėtas) užsakymus

## Duomenų Bazė

Pluginas sukuria lentelę `wp_dokan_voucher_logs`:

```sql
CREATE TABLE wp_dokan_voucher_logs (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  order_id BIGINT,
  vendor_id BIGINT,
  voucher_code VARCHAR(255),
  voucher_amount DECIMAL(10,2),
  status VARCHAR(50),
  created_at DATETIME,
  updated_at DATETIME
);
```

## Žinomas Funkcionalumas

- **Voucher validavimas** - Tikrina WooCommerce PDF Vouchers duomenis
- **Automatinis Order Completion** - Orderis keičiamas į completed
- **Naudojimo logai** - Visos operacijos saugomos

## Nereikalauta Funkcionalumo

⚠️ Komisiniu automatinis skaičiavimas - naudotojai patys turi administruoti komisijas

## Troubleshooting

### "Dokan Voucher Integration reikalingas Dokan pluginas!"

Įsitikinkite, jog Dokan yra įdiegtas ir aktyvuotas.

### Kuponas rodo "neteisingas kodas"

1. Patikrinkite ar kupono kodas teisingas
2. Patikrinkite ar WooCommerce PDF Vouchers rodo šį kuponą
3. Patikrinkite kupono statusą (gali būti inaktyvi)

### Orderis nepasikeičia į completed

1. Patikrinkite ar vendor turi teisę redaguoti orderį
2. Patikrinkite WooCommerce order statuses
3. Patikrinkite server logs

## Parama

Jei turite klausimų arba problemų:
1. Patikrinkite WordPress logs: `/wp-content/debug.log`
2. Patikrinkite browser console (F12 → Console)
3. Kontaktuokite dizainerį

## Versija

**v1.0.0** - Pirma versija

## Licencija

GPL v2 or later

---

**Kūrėjas:** Dokan Team  
**Apimis:** uzsakykmasaza.lt
