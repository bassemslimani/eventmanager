/**
 * Professional Badge Generator
 * Generates print-ready PDF badges with front and back pages
 */

export interface BadgeData {
    success: boolean;
    attendee: {
        name: string;
        name_ar: string | null;
        company: string | null;
        company_ar: string | null;
        type: string;
        email: string;
        qr_uuid: string;
    };
    event: {
        name: string;
        name_ar: string | null;
        logo_url: string | null;
        date: string;
        location: string;
    };
    template: {
        front_template_url: string | null;
        back_template_url: string | null;
        badge_width: number;
        badge_height: number;
        font_family: string;
        primary_color: string;
        secondary_color: string;
        terms_and_conditions: string | null;
        show_qr_code: boolean;
        show_logo: boolean;
        show_category_badge: boolean;
    };
    qr_code_svg: string;
}

/**
 * Convert SVG to Data URL
 */
function svgToDataURL(svgString: string): string {
    return 'data:image/svg+xml;base64,' + btoa(svgString);
}

/**
 * Generate print-ready PDF badge
 */
export async function generatePDFBadge(data: BadgeData): Promise<void> {
    try {
        console.log('Importing jsPDF...');
        const { jsPDF } = await import('jspdf');

        console.log('Creating PDF document...');
        const pdf = new jsPDF({
            orientation: 'portrait',
            unit: 'px',
            format: [data.template.badge_width, data.template.badge_height],
        });

        // Generate Front Page
        console.log('Generating front page...');
        await generateBadgeFront(pdf, data);
        console.log('Front page generated successfully');

        // Add new page for back
        console.log('Adding back page...');
        pdf.addPage([data.template.badge_width, data.template.badge_height]);

        // Generate Back Page
        console.log('Generating back page...');
        await generateBadgeBack(pdf, data);
        console.log('Back page generated successfully');

        // Download the PDF
        const filename = `badge-${data.attendee.name.replace(/\s+/g, '-')}.pdf`;
        console.log('Saving PDF as:', filename);
        pdf.save(filename);
        console.log('PDF saved successfully');
    } catch (error) {
        console.error('Error in generatePDFBadge:', error);
        throw error;
    }
}

/**
 * Generate Front Side of Badge
 */
async function generateBadgeFront(pdf: any, data: BadgeData): Promise<void> {
    const { template, attendee, event } = data;
    const width = template.badge_width;
    const height = template.badge_height;

    console.log('Front page dimensions:', width, 'x', height);

    // Draw background template if available
    if (template.front_template_url) {
        console.log('Loading front template:', template.front_template_url);
        try {
            await addImageToPDF(pdf, template.front_template_url, 0, 0, width, height);
            console.log('Front template loaded successfully');
        } catch (error) {
            console.error('Failed to load front template:', error);
            // Draw white background as fallback
            pdf.setFillColor(255, 255, 255);
            pdf.rect(0, 0, width, height, 'F');
            console.log('Using white background as fallback');
        }
    } else {
        console.log('No front template, using white background');
        // White background
        pdf.setFillColor(255, 255, 255);
        pdf.rect(0, 0, width, height, 'F');
    }

    // Add Event Logo if available
    if (template.show_logo && event.logo_url) {
        console.log('Loading event logo:', event.logo_url);
        try {
            await addImageToPDF(pdf, event.logo_url, width / 2 - 50, 30, 100, 50);
            console.log('Event logo loaded successfully');
        } catch (error) {
            console.error('Failed to load event logo:', error);
        }
    }

    // Add Event Title
    pdf.setFont(template.font_family || 'helvetica', 'bold');
    pdf.setFontSize(14);
    pdf.setTextColor(...hexToRGB(template.secondary_color));
    pdf.text(event.name, width / 2, 100, { align: 'center' });

    // Add Attendee Name
    pdf.setFont(template.font_family || 'helvetica', 'bold');
    pdf.setFontSize(24);
    pdf.setTextColor(...hexToRGB(template.primary_color));
    pdf.text(attendee.name, width / 2, height / 2 - 40, { align: 'center' });

    // Add Company if available
    if (attendee.company) {
        pdf.setFont(template.font_family || 'helvetica', 'normal');
        pdf.setFontSize(16);
        pdf.setTextColor(...hexToRGB(template.secondary_color));
        pdf.text(attendee.company, width / 2, height / 2, { align: 'center' });
    }

    // Add QR Code with primary color (if enabled)
    if (template.show_qr_code) {
        console.log('Adding QR code to front page');
        const qrSize = 120;
        const qrX = width / 2 - qrSize / 2;
        const qrY = height - qrSize - 80;

        // Convert SVG QR code to image and add to PDF
        const qrDataUrl = svgToDataURL(data.qr_code_svg);
        console.log('QR code data URL length:', qrDataUrl.length);
        try {
            pdf.addImage(qrDataUrl, 'SVG', qrX, qrY, qrSize, qrSize);
            console.log('QR code added successfully');
        } catch (error) {
            console.error('Failed to add QR code:', error);
        }

        // Add QR UUID text below QR code for manual entry
        pdf.setFont(template.font_family || 'helvetica', 'normal');
        pdf.setFontSize(10);
        pdf.setTextColor(...hexToRGB(template.primary_color));
        pdf.text(attendee.qr_uuid, width / 2, qrY + qrSize + 15, { align: 'center' });

        // Add helper text
        pdf.setFontSize(8);
        pdf.setTextColor(100, 100, 100);
        pdf.text('Scan QR or enter code manually', width / 2, qrY + qrSize + 28, { align: 'center' });
        console.log('QR code UUID and helper text added');
    }
}

/**
 * Generate Back Side of Badge
 */
async function generateBadgeBack(pdf: any, data: BadgeData): Promise<void> {
    const { template, attendee, event } = data;
    const width = template.badge_width;
    const height = template.badge_height;

    console.log('Back page dimensions:', width, 'x', height);

    // Draw background template if available
    if (template.back_template_url) {
        console.log('Loading back template:', template.back_template_url);
        try {
            await addImageToPDF(pdf, template.back_template_url, 0, 0, width, height);
            console.log('Back template loaded successfully');
        } catch (error) {
            console.error('Failed to load back template:', error);
            // Draw white background as fallback
            pdf.setFillColor(255, 255, 255);
            pdf.rect(0, 0, width, height, 'F');
            console.log('Using white background as fallback');
        }
    } else {
        console.log('No back template, using white background');
        // White background
        pdf.setFillColor(255, 255, 255);
        pdf.rect(0, 0, width, height, 'F');
    }

    // Add Event Info
    pdf.setFont(template.font_family || 'helvetica', 'bold');
    pdf.setFontSize(16);
    pdf.setTextColor(...hexToRGB(template.primary_color));
    pdf.text(event.name, width / 2, 50, { align: 'center' });

    pdf.setFont(template.font_family || 'helvetica', 'normal');
    pdf.setFontSize(12);
    pdf.setTextColor(...hexToRGB(template.secondary_color));
    pdf.text(event.location, width / 2, 75, { align: 'center' });
    pdf.text(event.date, width / 2, 95, { align: 'center' });

    // Add Terms and Conditions if available
    if (template.terms_and_conditions) {
        pdf.setFont(template.font_family || 'helvetica', 'normal');
        pdf.setFontSize(10);
        pdf.setTextColor(60, 60, 60);

        const terms = pdf.splitTextToSize(template.terms_and_conditions, width - 40);
        pdf.text(terms, width / 2, height / 2, { align: 'center', maxWidth: width - 40 });
    }

    // Add Contact Info
    pdf.setFont(template.font_family || 'helvetica', 'normal');
    pdf.setFontSize(9);
    pdf.setTextColor(100, 100, 100);
    pdf.text('Email: ' + attendee.email, width / 2, height - 40, { align: 'center' });
}

/**
 * Helper function to add image to PDF
 */
async function addImageToPDF(pdf: any, imageUrl: string, x: number, y: number, width: number, height: number): Promise<void> {
    return new Promise((resolve, reject) => {
        const img = new Image();

        // Set timeout for image loading
        const timeout = setTimeout(() => {
            reject(new Error('Image load timeout'));
        }, 10000); // 10 second timeout

        img.crossOrigin = 'anonymous';

        img.onload = () => {
            clearTimeout(timeout);
            try {
                // Detect image format from URL
                const format = imageUrl.toLowerCase().endsWith('.png') ? 'PNG' : 'JPEG';
                pdf.addImage(img, format, x, y, width, height);
                resolve();
            } catch (error) {
                reject(error);
            }
        };

        img.onerror = (error) => {
            clearTimeout(timeout);
            reject(error);
        };

        // Add timestamp to prevent caching issues
        const separator = imageUrl.includes('?') ? '&' : '?';
        img.src = imageUrl + separator + 't=' + Date.now();
    });
}

/**
 * Convert hex color to RGB
 */
function hexToRGB(hex: string): [number, number, number] {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result
        ? [parseInt(result[1], 16), parseInt(result[2], 16), parseInt(result[3], 16)]
        : [0, 0, 0];
}
