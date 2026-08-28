<?php

namespace Database\Seeders;

use App\Models\AppointmentSlot;
use App\Models\Report;
use Illuminate\Database\Seeder;

class ReportSeeder extends Seeder
{
    public function run(): void
    {
        $samples = [
            [
                'title' => 'Visit Report — CCTV & Network Survey',
                'content' => "Site survey for CCTV and network infrastructure was completed at the client premises. The purpose of this visit was to evaluate the current security coverage, identify blind spots, and prepare a clear technical recommendation for a full surveillance upgrade.\n\n"
                    . "During the inspection, the engineering team reviewed the main entrance, parking area, corridors, and rear access points. Existing camera positions were checked for angle, height, and lighting conditions. Several areas were found to have weak night visibility, and we recommended relocating two cameras and adding infrared coverage for better results after sunset.\n\n"
                    . "The network rack location was also inspected. We confirmed that there is enough space for an NVR unit near the distribution switch, with access to stable power and cable pathways. A provisional layout for eight cameras was discussed with the client and approved in principle, pending final quotation.\n\n"
                    . "Cabling routes were marked on the architectural plan, including vertical shafts and ceiling trays. The client requested that all visible cabling in living areas be concealed. We agreed that conduits will be used in finished zones and surface trunking only in technical rooms.\n\n"
                    . "Next steps: prepare a detailed quotation covering cameras, NVR, hard disk storage, networking accessories, labor, and commissioning. A follow-up installation visit will be scheduled after client approval of the commercial offer.",
                'client_name' => 'Mohamed Abdullah Al Suwaidi',
                'engineer_name' => 'Khaled Hassan',
                'visit_date' => '2026-08-20',
            ],
            [
                'title' => 'Maintenance Report — Smart Lighting',
                'content' => "Scheduled maintenance for the smart lighting system was carried out successfully. The client reported intermittent failures on two wall switches in the living room and delayed response from some Zigbee devices after recent power interruptions.\n\n"
                    . "Upon arrival, the engineer performed a full system health check on the hub, wireless mesh strength, and device pairing status. Two faulty smart switches were replaced with new units of the same model. All related loads (spotlights and decorative lighting) were tested under normal and dimming modes.\n\n"
                    . "Zigbee devices that had lost connection were factory-reset and re-paired to the hub. Channel interference was reviewed, and the hub was moved slightly to improve wireless coverage toward the upper floor. After reconfiguration, response time returned to normal levels across all tested rooms.\n\n"
                    . "Automation scenes were verified one by one: Good Night, Away Mode, and Cinema Mode. Each scene executed the expected combination of lights, curtains, and secondary devices without conflict. The client was shown how to edit scene timing from the mobile application if needed in the future.\n\n"
                    . "Final confirmation: the smart lighting system is operating normally. No further hardware replacement is required at this stage. A preventive maintenance reminder was recommended after six months, or earlier if new devices are added to the network.",
                'client_name' => 'Sara Al Mansoori',
                'engineer_name' => 'Ahmed Farouk',
                'visit_date' => '2026-08-22',
            ],
            [
                'title' => 'Visit Report — Intercom & Gate Control',
                'content' => "Installation inspection and handover for the video intercom and gate control system were completed at the project site. The visit focused on verifying video quality, audio clarity, remote unlocking, and gate motor integration with the indoor monitor and mobile application.\n\n"
                    . "The outdoor station video feed was tested under daylight and shaded conditions. Image quality was clear, and two-way audio communication with the indoor monitor worked without echo or delay. Door unlock commands from both the indoor handset and the mobile app were confirmed successfully.\n\n"
                    . "Gate motor control was checked for open, close, and stop operations. Remote buttons and app triggers responded correctly. A minor adjustment was applied to the door strike voltage to ensure reliable latch release without excessive current draw. Limit switches and safety edges were also inspected and found in good condition.\n\n"
                    . "The client and family members received on-site training covering basic call answering, remote door opening, visitor history review, and emergency unlock procedures. Printed quick-start notes were left with the client for future reference.\n\n"
                    . "Conclusion: the intercom and gate control system is ready for daily use. Warranty terms and support contacts were explained. Any additional requests (extra indoor monitors or integration with CCTV) can be quoted separately upon client request.",
                'client_name' => 'bilal',
                'engineer_name' => 'smart',
                'visit_date' => '2026-08-28',
            ],
        ];

        $booked = AppointmentSlot::where('status', 'booked')
            ->orderBy('date')
            ->get();

        foreach ($samples as $index => $sample) {
            $slot = $booked->get($index);

            $client = $slot?->customer_name ?: $sample['client_name'];
            $engineer = $slot?->engineer_name ?: $sample['engineer_name'];
            $date = $slot?->date?->format('Y-m-d') ?: $sample['visit_date'];

            Report::updateOrCreate(
                [
                    'title' => $sample['title'],
                    'visit_date' => $date,
                    'client_name' => $client,
                ],
                [
                    'appointment_slot_id' => $slot?->id,
                    'content' => $sample['content'],
                    'engineer_name' => $engineer,
                    'images' => [],
                ]
            );
        }

        $this->command?->info('Seeded ' . count($samples) . ' visit reports with detailed content.');
    }
}
