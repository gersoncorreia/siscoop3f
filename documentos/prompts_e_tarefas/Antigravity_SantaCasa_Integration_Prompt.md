```
Role: Senior SaaS multi-tenant software development expert in PHP Laravel 12, PostgreSQL/MySQL, and AI integration for prototyping.

Task: Integrate the 'Clube Saúde Santa Casa da Amazônia' program as a new tenant/module within an existing multi-tenant SaaS platform. The integration should cover all specified functionalities, financial models, member journeys, and operational details. Focus on configuring the existing system to support Santa Casa's specific requirements.

## Santa Casa da Amazônia Program Details:

*   **Program Name**: Clube Saúde Santa Casa da Amazônia
*   **Nature**: Relationship, loyalty, and benefits program for accessing Santa Casa's own health services. **Not** a private health plan, nor a substitute for SUS/ANS. Requires clear communication to members about program nature, services, values, conditions, and limitations.
*   **Technology Integration Focus**: Association, member identification, digital ID card, scheduling, consultations, exams, procedures, elective hospitalizations, telemedicine (when available), communication, relationship management, prevention campaigns, administrative management.
*   **Access Channels**: Computer, tablet, or mobile.
*   **Target Audience**: Individuals, families, self-employed, public servants, companies, associations, cooperatives, unions, public/private institutions. Supports individual or collective/institutional memberships.
*   **Operational Pillars**: Association (digital ID card), Access (services via scheduling, availability, payment per table), Relationship (continuous communication, campaigns, follow-ups).

## Platform Functionalities for Santa Casa:

*   Member registration, digital membership, dependent registration, digital ID card, cadastral status control.
*   Digital agenda, scheduling (consultations, exams, procedures, elective hospitalizations).
*   Service management, service utilization control.
*   Member communication, managerial reports, user management, permission control.
*   Performance indicators, relationship tools, prevention/promotion campaigns.
*   Integration with Santa Casa administrative processes.

## Member Benefits (Configurable):

*   Medical consultations, emergency care, telemedicine, lab exams, imaging exams, preventive check-ups.
*   Outpatient procedures, minor surgeries, elective surgeries, hospitalizations, physiotherapy, dental services.
*   Online scheduling, digital ID card, prevention campaigns, health promotion actions.
*   Values, conditions, availability, hours, and criteria defined by Santa Casa's internal table.

## Financial Model:

*   **Revenue Sources**: Monthly fees/association fees (if applicable), utilization of Santa Casa's services, corporate/institutional contracts, agreements with companies/associations/cooperatives/unions, special programs.
*   **Objective**: Recurring revenue, increased capacity utilization, financial sustainability for Santa Casa.

## Member Journey:

1.  **Associate**: Interested party joins.
2.  **Receive Digital ID Card**: Member gets digital identification.
3.  **Choose Service**: Selects consultation, exam, procedure, or other available service.
4.  **Schedule**: Member schedules the chosen service.
5.  **Utilize**: Attends Santa Casa and uses the service under current conditions.
6.  **Monitor**: Can track appointments and information in the system.
7.  **Maintain Relationship**: Receives institutional information and health promotion/prevention campaigns.

## Differentials (to be highlighted in system configuration/branding):

*   Santa Casa's own services/hospital structure, emergency care, telemedicine.
*   Integrated digital platform, online scheduling, digital ID card, digital management.
*   Direct relationship, humanized care, multidisciplinary team, information security, ease of access.
*   Prevention campaigns, individual/family/corporate/institutional contract options.

## Performance Indicators (for tenant admin dashboards):

*   Number of members, new memberships, cancellations, base growth.
*   Service utilization (consultations, exams, procedures, scheduling, attendance rate, telemedicine usage).
*   Revenue generated, recurring revenue, member satisfaction, loyalty index, acquisition channel performance.

## Initial Service Table (for configuration):

| Nº | Produto/Serviço                      | Valor (R$) |
|----|--------------------------------------|------------|
| 1  | Consulta com clínico geral           | 80.00      |
| 2  | Exame laboratorial básico            | 80.00      |
| 3  | Pronto atendimento                   | 80.00      |
| 4  | Atestado simples                     | 80.00      |
| 5  | Atestado especializado               | 300.00     |
| 6  | Enfermaria – diária                  | 100.00     |
| 7  | Apartamento – diária                 | 200.00     |
| 8  | Ultrassonografia                     | 80.00      |
| 9  | RX de tórax                          | 80.00      |
| 10 | RX de membros                        | 50.00      |
| 11 | Tomografia                           | 300.00     |
| 12 | Tomografia com contraste e laudo     | 500.00     |
| 13 | Pequena cirurgia                     | 300.00     |
| 14 | Anestesia para cirurgia simples      | 200.00     |
| 15 | Anestesia para cirurgia normal       | 800.00     |
| 16 | Coleta de sangue para exames         | 50.00      |
| 17 | Fisioterapia – sessão                | 70.00      |
| 18 | Serviço odontológico – extração/limpeza simples | 100.00     |
| 19 | Cartão Santa Casa – pessoa física (itens 1-18) | 50.00      |
| 20 | Cartão Santa Casa – família (2+ dependentes, itens 1-18) | 30.00      |

*Note: This table is an initial reference and may be updated by Santa Casa's administration.* 

## Access Levels (RBAC - for system user roles):

*   **Attendant**: Operational level, limited to service and operation recording.
*   **Operational Manager**: Tactical level, for monitoring, supervision, management, and control of operational activities.
*   **Provider**: Strategic level, for managerial and administrative oversight.
*   **Council**: Strategic level, based on institutional attributions and defined permissions.

```
