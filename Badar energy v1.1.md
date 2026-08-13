# Badar Energy Management System

## Business Process & Functional Requirements

### Version 1.1 — Updated After Feedback

## 1. Document Information

| Detail | Information |
| --- | --- |
| Project | Badar Energy Management System |
| Document | Business Process & Functional Requirements |
| Version | 1.1 |
| Previous Version | 1.0 |
| Status | Updated –Review |
| Date | 07 August 2026 |
| Prepared By | Software Development Team |

# 2. Purpose of This Document

Is document ka purpose Badar Energy Management System ke business process ko simple aur clear way mein define karna hai.
Is document mein stock receive hone se lekar:
- Warehouse mein stock available hone
- OEM stock handle hone
- Assembly process
- Customer sale
- Sale approval
- Delivery
- Invoice
- Gate Pass
- Stock movement
- Internal product issue
- Dealer / Owner ledger
tak ka complete process describe kiya gaya hai.
Previous document ke review ke baad Badar Energy Management ki taraf se diye gaye changes aur confirmations ko is version mein update kar diya gaya hai.
Jahan kisi requirement ki additional clarification abhi required hai, usay Pending Confirmation ke taur par clearly mention kiya gaya hai.
# 3. Changes From Previous Version

Previous version ke review ke baad following changes aur clarifications incorporate kiye gaye hain:
- Dealer, Distributor aur Walk-in / End Customer ki sales Head Office se process hongi.
sirf walk in customer ki sale outlet se hogi
- In sales ke according stock inventory se minus hoga.
- End Customer ke Sale Order ke liye approval required hogi.
- Approval mein price aur quantity ko check kiya jayega.
- Approved Sale Order ke against Delivery Order (DO) create hoga.
- DO ke baad required serial number scanning, stock deduction, invoice aur Gate Pass ka process complete hoga.
- Normal sales ke ilawa Screen, BMS, Expo Center aur other stock movements ke liye manually Gate Pass create kiya ja sakega.
- Kisi bhi person ko product issue karne par Delivery Order create hoga.
- Owner / Care Of ke liye separate ledger maintain kiya jayega.
- Stock receive hone ke baad product Warehouse, OEM Inventory ya Assembly Line mein ja sakta hai.
- Complete / ready products Warehouse mein receive honge.
- OEM products separate inventory mein quantity basis par maintain honge.
- OEM products ke individual Serial Numbers required nahi honge.
- OEM products ke liye Batch Number maintain kiya jayega. Batch tracking ki additional details confirmation ke liye pending hain.
- Assembly ke raw parts individual Serial Number ke baghair quantity basis par maintain honge.
- OEM products, including products such as Trinex, raw parts category mein aa sakte hain.
- Assembly flexible/custom basis par ho sakti hai.
- Assembly ke raw parts ka stock manually deduct kiya jayega.
- Finished product Assembly ke baad Warehouse mein intake hoga.
- Warehouse intake ke baad finished product ka barcode generate hoga.
- Assembly ke dauran defective parts ko Fault mein move kiya jayega aur available quantity reduce hogi.
# 4. Overall Stock Receiving Process

Jab stock business ko receive hota hai, to stock ki type aur requirement ke according uska process decide hoga.
Stock following locations/processes mein ja sakta hai:
### A. Warehouse

Agar complete / ready product receive hota hai.
### B. OEM Inventory

Agar product OEM / non-serialized category ka hai.
### C. Assembly Line

Agar product raw parts ki form mein receive hota hai aur un parts ko assemble karke finished product banana hai.
# 5. Warehouse Stock Process

Agar complete / ready product receive hota hai:
Stock Receive
↓
Warehouse Intake
↓
Barcode Generation
↓
Warehouse Stock Available
Iske baad product sale ya kisi other approved stock movement ke liye available hoga.
# 6. OEM Product Process

OEM products ko normal serialized products se separate handle kiya jayega.
OEM products ke liye:
- Individual Serial Number required nahi hoga.
- Stock quantity ke basis par maintain hoga.
- OEM stock separate inventory mein maintain hoga.
- OEM product ke liye Batch Number maintain kiya jayega.
- Required approval ke baad OEM stock further process ke liye available hoga.
### OEM Product Example

Agar OEM product ki 100 quantity receive hoti hai:
OEM Inventory
Product: XYZ
Quantity: 100
Batch Number: To be maintained
Individual Serial Numbers: Not Required
# 7. OEM Batch Tracking — Pending Clarification

apki confirmation ke mutabiq OEM products ke liye individual Serial Number maintain nahi kiya jayega aur Batch Number maintain kiya jayega.
Lekin Batch Number ke exact process ko final karne ke liye following points ki confirmation required hai:
Qno : muje ap batch number kese handle kerte he wo btaden 
generate krenge explample 1 2 3:____________________________________________________________________________________
____________________________________________________________________________________
### Q-019 — OEM Batch Number Process

- Batch Number supplier ki taraf se provide kiya jayega ya system automatically generate karega? (AUTO)
- Kya same product ke multiple batches maintain kiye jayenge? NO
- Kya sale ya stock movement ke waqt Batch Number select karna mandatory hoga? YES
- Kya Return / Warranty ke waqt Batch Number ke through product verify kiya jayega? YES
- Kya Batch Number ke saath manufacturing date, expiry date ya koi other information maintain karni hai? YES
Status: 🟡 Pending Confirmation
Response:
# 8. Assembly Line & Raw Parts

Agar stock raw parts ki form mein receive hota hai to woh Assembly Line ke process mein jayega.
Examples:
- Casing
- Battery Cells
- LCD
- BMS
- Other required components
Raw parts:
- Quantity basis par maintain honge. YES
- Individual Serial Number required nahi hoga. YES
- Assembly ke liye use kiye jayenge. YES
- Assembly complete hone par required quantity manually deduct ki jayegi. YES
# 9. Raw Parts — Serial Number

Raw parts ke individual Serial Numbers maintain nahi kiye jayenge. YES
Example:
Agar 500 Cells receive hue:
Cell Quantity = 500
Individual:
❌ Cell-001❌ Cell-002❌ Cell-003
ki tarah individual serial tracking required nahi hogi.
Raw parts quantity ke basis par maintain honge.
# 10. OEM Products as Raw Parts

OEM products, including products such as Trinex, bhi raw parts category mein aa sakte hain. YES
Yani raw parts sirf Badar Energy ke apne products tak limited nahi honge. YES
Business requirement ke according OEM products bhi Assembly process mein raw parts ke taur par use kiye ja sakte hain. YES
# 11. Assembly Process

Assembly Line mein available raw parts ko use karke finished product tayyar kiya jayega. YES
Assembly fixed recipe tak limited nahi hogi. YES
Business requirement ke according different parts ko combine karke custom product assemble kiya ja sakega. YES
### Example

Casing + Cells + LCD + BMS + Other Required Parts
↓
Finished Battery / Product
# 12. Flexible / Custom Assembly

System mein har product ke liye fixed recipe mandatory nahi hogi.
Business requirement ke according assembly mein required parts select karke product prepare kiya ja sakega.
Example:
Ek product ke liye required parts business requirement ke mutabiq change ho sakte hain.
Isliye Assembly process flexible / custom basis par maintain ki jayegi.
# 13. Raw Parts Stock Deduction

Assembly complete hone par raw parts ka stock automatically deduct nahi hoga.
Raw parts ki used quantity manually deduct ki jayegi.
### Example

Assembly mein:
- Casing = 1
- Cells = 16
- LCD = 1
use hua.
To system mein relevant quantities manually reduce ki jayengi.
# 14. Finished Product After Assembly

Jab Assembly Line mein finished product ready ho jaye:
Finished Product Ready
↓
Warehouse mein Stock Intake
↓
Barcode Generation
↓
Warehouse Stock Available
Finished product Warehouse mein intake hone ke baad normal Warehouse stock ka part ban jayega.
# 15. Finished Product Barcode

Finished product ka barcode Assembly Line par directly generate nahi hoga.
Product ready hone ke baad:
Assembly Line
↓
Warehouse Intake
↓
Barcode Generation
Barcode Warehouse intake ke waqt generate kiya jayega.
# 16. Fault / Defective Parts

Agar Assembly ke dauran koi part faulty ya defective ho jaye, to us part ko Fault mein move kiya jayega.
Fault mein move hone ke baad:
- Available quantity reduce hogi.
- Fault quantity mein item show hoga.
- Fault ka separate record maintain hoga.
### Example

Available Cells:
100
Assembly ke dauran Faulty Cells:
2
Result:
Available Quantity = 98
Fault Quantity = 2
# 17. Complete Assembly Flow

Raw Parts Receive
↓
Assembly Line
↓
Parts Assemble
↓
Raw Parts Manually Deduct
↓
Finished Product Ready
↓
Warehouse Intake
↓
Barcode Generation
↓
Warehouse Stock Available
↓
Normal Sale / Dispatch Process
# 18. Customer / Party Types

System mein sales aur product issue ke liye following customer / party types maintain ki jayengi:
- Dealer
- Distributor
- Walk-in / End Customer
- Care Of / Owner
# 19. Dealer

Dealer regular business customer hoga jo Badar Energy se products purchase karta hai.
Dealer ke liye:
- Credit Limit maintain hogi.
- Sale Order create hoga.
- Approval process follow hoga.
- Sales record maintain hoga.
- Payment record maintain hoga.
- Outstanding amount track hoga.
- Dealer Ledger maintain hoga.
### Example

Dealer ne 50 batteries purchase ki aur complete payment immediately nahi ki.
System mein:
Sale → Payment → Outstanding → Credit Position
Dealer ke ledger mein maintain rahega.
# 20. Distributor

Distributor ko separate internal outlet / branch stock movement ke taur par treat nahi kiya jayega.
confirmation ke mutabiq:
Dealer / Distributor / Walk-in Customer ki sale Head Office se process hogi.
Sale ke according relevant stock Head Office inventory se minus hoga.
# 21. Walk-in / End Customer

End Customer wo customer hoga jo directly Badar Energy se product purchase karta hai.
End Customer ke liye:
- Sale Order create hoga.
- Customer select krenge to wahan name aur customer details add krenge
- Sale Order approval required hogi.
- Price aur quantity approval mein check ki jayegi.
- Approval ke baad Delivery Order create hoga.
- Required stock inventory se minus hoga.
- Invoice create hogi.
- Gate Pass create hoga.
- Product warehouse se bahar jayega.
# 23. Care Of / Owner Consumption

Care Of ka use un products ke liye hoga jo:
- Business ke andar use ho rahe hon.
- Kisi person ko issue kiye ja rahe hon.
- Kisi department ko issue kiye ja rahe hon.
- Office / company use ke liye diye ja rahe hon.
### Examples

- Office ke liye battery
- Company vehicle ke liye product
- Employee ko product issue
- Department ko product issue
- Kisi person ko product issue
# 24. Product Issue ke Liye Delivery Order

Jo bhi product kisi bhi person ko issue kiya jayega, uske against Delivery Order (DO) create hoga.
Iska purpose ye hai ke har issued product ka proper record system mein available rahe.
# 25. Owner / Care Of Ledger

Owner / Care Of ke liye separate ledger maintain kiya jayega.
Is ledger mein issued products aur related payment information track ki ja sakegi.
Future mein required payment ko ledger ke through adjust kiya ja sakega.
# 26. Sale Order

Sale Order create karte waqt relevant customer / party select ki jayegi.
Sale Order mein following information maintain hogi:
- Customer
- Product
- Quantity
- Price
- Other required details
# 27. Sale Order Approval

Sale Order ko approval process se guzarna hoga.
Approval ke waqt specially:
- Price
- Quantity
- Other relevant sale details
check ki jayengi.
Approval ke baad hi Sale Order further delivery process mein jayega.
# 28. Dealer Credit Limit

Agar customer Dealer hai, to system uski predefined Credit Limit check karega.
System mein Dealer ki:
- Credit Limit
- Previous Sales
- Payments
- Outstanding
- Remaining Credit Position
maintain hogi.
Is se ye check kiya ja sakega ke Dealer ki current credit position ke according further sale process ki ja sakti hai ya nahi.
# 29. Delivery Order (DO)

Approved Sale Order ke against Delivery Order create hoga.
DO mein clear hoga:
- Kis customer ko product dena hai
- Kaunsa product dena hai
- Kitni quantity deni hai
- Kaunsa stock dispatch karna hai
# 30. DO ke Baad Sales Process

Normal sale ke case mein Delivery Order ke baad following process complete hoga:
Delivery Order
↓
Serial Number Scan — Where Required
↓
Stock Minus
↓
Invoice
↓
Gate Pass
↓
Stock Warehouse se Bahar
# 31. Serial Number Handling

Jin products ko Serial Number ke saath maintain karna required hai, unka Serial Number dispatch ke process mein scan kiya jayega.
Is se system mein exact product ki history maintain rahegi.
OEM products ke liye individual Serial Number scanning required nahi hogi.
Raw parts ke liye bhi individual Serial Number required nahi hoga.
# 32. Stock Minus

Sale / dispatch process complete hone par relevant stock inventory se minus kiya jayega.
Example:
Available Stock:
100
Sale Quantity:
10
Remaining Stock:
90
System mein stock movement ka record maintain rahega.
# 33. Invoice

Sale process ke according Invoice create ki jayegi.
Invoice mein sale ke relevant:
- Customer
- Product
- Quantity
- Price
- Amount
ka record maintain hoga.
# 34. Gate Pass

Gate Pass warehouse se stock bahar jane ka official record hoga.
Normal sales ke ilawa manually Gate Pass following situations mein bhi create kiya ja sakega:
- Screen
- BMS
- Expo Center
- Other required stock movements
Is se warehouse se bahar jane wale stock ka proper record maintain rahega.
# 35. Manual Gate Pass Approval — Pending Confirmation

Agar Screen, BMS, Expo Center ya kisi other purpose ke liye manually Gate Pass create kiya jaye, to following point ki confirmation required hai:
### Q-025 — Manual Gate Pass Approval

Kya manually create kiye gaye Gate Pass ko stock warehouse se bahar jane se pehle approve karna mandatory hoga?
Options:
☐ Yes — Approval mandatory hoga.
☐ No — Gate Pass approval ke baghair process ho sakta hai.
☐ Other:
Status: 🟡 Pending Confirmation
answer : related to battery parts ke liye required hoga approved otherwise ni
# 36. Complete Sales Flow

### Dealer / Distributor / Walk-in Customer

Customer Create / Select
↓
Sale Order Create
↓
Approval
↓
Delivery Order
↓
Serial Scan — Where Required
↓
Stock Minus
↓
Invoice
↓
Gate Pass
↓
Stock Warehouse se Bahar
↓
Relevant Ledger Update — Where Applicable
# 37. Dealer Sale Flow

Dealer
↓
Sale Order
↓
Credit Limit Check
↓
Approval
↓
Delivery Order
↓
Serial Scan — Where Required
↓
Stock Minus
↓
Invoice
↓
Gate Pass
↓
Dealer Ledger Update
# 38. End Customer Sale Flow

End Customer
↓
Sale Order
↓
Approval
↓
Price / Quantity Check
↓
Delivery Order
↓
Serial Scan — Where Required
↓
Stock Minus
↓
Invoice
↓
Gate Pass
↓
Stock Warehouse se Bahar
# 39. Owner / Care Of Product Issue Flow

Person / Department / Owner
↓
Product Issue
↓
Delivery Order
↓
Stock Minus
↓
Gate Pass
↓
Owner / Care Of Ledger Update
# 40. Complete Stock Movement Overview

### Ready Product

Shipment / Stock Receive
↓
Warehouse Intake
↓
Barcode Generation
↓
Warehouse Stock Available
↓
Sale / Issue / Other Movement
### OEM Product

Stock Receive
↓
OEM Inventory
↓
Quantity Based Stock
↓
Batch Number
↓
Required Approval
↓
Sale / Further Movement
### Assembly Product

Raw Parts Receive
↓
Assembly Line
↓
Parts Assemble
↓
Raw Parts Manual Deduction
↓
Finished Product Ready
↓
Warehouse Intake
↓
Barcode Generation
↓
Warehouse Stock Available
↓
Sale / Dispatch
# 41. Final Confirmed Business Rules

# Following requirements have been confirmed and incorporated into this version:

# ☐ Dealer / Distributor / Walk-in Customer ki sale Head Office se process hogi and outlet aswell

# ☐ Relevant sale ke according stock inventory se minus hoga.

# ☐ End Customer ke Sale Order ke liye approval required hogi.

# ☐ Approval mein price aur quantity check ki jayegi.

# ☐ Approved Sale Order ke against Delivery Order create hoga.

# ☐ Required products ka Serial Number scan kiya jayega.

# ☐ Stock minus kiya jayega.

# ☐ Invoice create hogi.

# ☐ Gate Pass create hoga.

# ☐ Screen, BMS, Expo Center aur other required stock movements ke liye manual Gate Pass create kiya ja sakega.

# ☐ Kisi bhi person ko product issue karne par Delivery Order create hoga.

# ☐ Owner / Care Of ke liye separate ledger maintain hoga.

# ☐ Dealer ke liye Credit Limit aur Dealer Ledger maintain hoga.

# ☐ OEM products separate inventory mein maintain honge.

# ☐ OEM products quantity basis par maintain honge.

# ☐ OEM products ke individual Serial Numbers required nahi honge.

# ☐ OEM products ke liye Batch Number maintain kiya jayega.

# ☐ OEM products, including Trinex, raw parts ke taur par Assembly mein use kiye ja sakte hain.

# ☐ Raw parts quantity basis par maintain honge.

# ☐ Raw parts ke individual Serial Numbers required nahi honge.

# ☐ Assembly flexible/custom basis par ho sakti hai.

# ☐ Assembly ke raw parts automatically deduct nahi honge.

# ☐ Raw parts manually deduct kiye jayenge.

# ☐ Finished product Assembly ke baad Warehouse mein intake hoga.

# ☐ Warehouse intake ke baad finished product ka barcode generate hoga.

# ☐ Assembly ke dauran faulty parts ko Fault mein move kiya jayega.

# ☐ Fault mein move hone par available quantity reduce hogi aur Fault quantity maintain hogi.

# 42. Pending Confirmations

Following points abhi final nahi hain aur confirmation required hai:
### Q-019 — OEM Batch Tracking

- Batch Number supplier provide karega ya system generate karega? (system)
- Multiple batches maintain honge? YES
- Sale / stock movement ke waqt Batch Number select karna mandatory hoga? YES
- Return / Warranty mein Batch Number se verification hogi? YES
- Batch ke saath additional information maintain karni hai? YES
Status: 🟡 Pending
### Q-025 — Manual Gate Pass Approval

Manually created Gate Pass ko stock warehouse se bahar jane se pehle approve karna mandatory hoga ya nahi? YES
Status: 🟡 Pending
# 43. Requirement & Scope Confirmation

approval ke baad is document mein described business requirements ko Approved Requirement Baseline maana jayega.
Approval ke baad agar koi new functionality ya existing requirement mein change required ho, to usay Change Request ke taur par separately review aur approve kiya jayega.
Is process ka purpose ye hai ke aur development team dono ke paas agreed requirements ka clear record available rahe.
# 44. Document Status

Current Version: V1.1
Status: 🟡 Review / Pending Confirmation
Next Version: V1.2
V1.2 will be prepared after receiving confirmation on the remaining pending questions and any additional feedback.