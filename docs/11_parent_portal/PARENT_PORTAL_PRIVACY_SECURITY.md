# Parent Portal Privacy & Security v1.0

## 1. Classification
Guardian identity/contact and Student reports are restricted personal data. Portal access is purpose-specific and child-specific.

## 2. Data minimization
A Guardian should receive only data required for the parent-facing purpose. Example: Academic report access does not require exposing internal Student database identifiers, NISN, audit history or staff notes unless explicitly part of the approved report.

## 3. Isolation requirements
Mandatory negative-security cases:
- Guardian A cannot enumerate Guardian B's children;
- Guardian A cannot access another Student by changing URL parameters;
- one child relationship does not grant access to siblings without their own relationship;
- expired/ended account link denies access;
- draft/reviewed/unpublished artifacts deny access;
- internal notes/alerts never appear in portal serialization.

## 4. Sensitive-domain boundary
Kepengasuhan, internal disciplinary cases and internal Early Warning alerts are not parent-visible by default. Any future exposure requires an explicit parent-facing publication workflow, not direct portal queries.

## 5. Download/export
Downloads should be minimized, authorization-checked, auditable, and optionally watermarked/document-coded in future. Whether downloads are allowed is policy-controlled.

## 6. No analytics leakage
Portal analytics must not be repurposed to infer or score Guardian behavior. Access logs are for security, support and service reliability, with retention and access controls.

## 7. Account recovery
Recovery must not let a person take over a Guardian account merely by knowing a Student name/NISN. Recovery uses verified identity/contact evidence and approved procedure.

## 8. AI boundary
Future Parent AI cannot use hidden/internal context unavailable in the Portal. Parent-facing AI responses must be grounded only in parent-authorized facts/artifacts for authorized children.
