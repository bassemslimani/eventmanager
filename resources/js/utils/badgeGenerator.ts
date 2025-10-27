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
        badge_width: number;  // legacy px
        badge_height: number;  // legacy px
        badge_width_cm: number;  // new cm measurement
        badge_height_cm: number;  // new cm measurement
        font_family: string;
        primary_color: string;
        secondary_color: string;
        terms_and_conditions: string | null;
        show_qr_code: boolean;
        show_logo: boolean;
        show_category_badge: boolean;
        elements?: any[];  // element-based layout
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
        // Use cm as the unit for standard badge size (8.5cm x 12.5cm)
        const badgeWidthCm = data.template.badge_width_cm || 8.5;
        const badgeHeightCm = data.template.badge_height_cm || 12.5;

        const pdf = new jsPDF({
            orientation: 'portrait',
            unit: 'cm',
            format: [badgeWidthCm, badgeHeightCm],
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
    const widthCm = template.badge_width_cm || 8.5;
    const heightCm = template.badge_height_cm || 12.5;

    console.log('Front page dimensions:', widthCm, 'cm x', heightCm, 'cm');

    // Draw background template if available
    if (template.front_template_url) {
        console.log('Loading front template:', template.front_template_url);
        try {
            await addImageToPDF(pdf, template.front_template_url, 0, 0, widthCm, heightCm);
            console.log('Front template loaded successfully');
        } catch (error) {
            console.error('Failed to load front template:', error);
            // Draw white background as fallback
            pdf.setFillColor(255, 255, 255);
            pdf.rect(0, 0, widthCm, heightCm, 'F');
            console.log('Using white background as fallback');
        }
    } else {
        console.log('No front template, using white background');
        // White background
        pdf.setFillColor(255, 255, 255);
        pdf.rect(0, 0, widthCm, heightCm, 'F');
    }

    // Add Event Logo if available (preserve aspect ratio)
    if (template.show_logo && event.logo_url) {
        console.log('Loading event logo:', event.logo_url);
        try {
            // Logo: 4cm wide, 2cm tall, centered horizontally
            await addImageToPDFWithAspectRatio(pdf, event.logo_url, widthCm / 2 - 2, 0.5, 4, 2);
            console.log('Event logo loaded successfully');
        } catch (error) {
            console.error('Failed to load event logo:', error);
        }
    }

    // Add Event Title
    pdf.setFont(template.font_family || 'helvetica', 'bold');
    pdf.setFontSize(16);
    pdf.setTextColor(...hexToRGB(template.secondary_color));
    pdf.text(event.name, widthCm / 2, 3, { align: 'center' });

    // Add Event Date and Location
    pdf.setFont(template.font_family || 'helvetica', 'normal');
    pdf.setFontSize(11);
    pdf.setTextColor(...hexToRGB(template.secondary_color));
    pdf.text(event.date, widthCm / 2, 3.7, { align: 'center' });
    pdf.text(event.location, widthCm / 2, 4.2, { align: 'center' });

    // Add Attendee Name
    pdf.setFont(template.font_family || 'helvetica', 'bold');
    pdf.setFontSize(24);
    pdf.setTextColor(...hexToRGB(template.primary_color));
    pdf.text(attendee.name, widthCm / 2, 5.5, { align: 'center' });

    // Add Company if available
    if (attendee.company) {
        pdf.setFont(template.font_family || 'helvetica', 'normal');
        pdf.setFontSize(16);
        pdf.setTextColor(...hexToRGB(template.secondary_color));
        pdf.text(attendee.company, widthCm / 2, 6.5, { align: 'center' });
    }

    // Add Attendee Type/Category
    pdf.setFont(template.font_family || 'helvetica', 'bold');
    pdf.setFontSize(12);
    pdf.setTextColor(...hexToRGB(template.primary_color));
    const typeLabel = attendee.type.charAt(0).toUpperCase() + attendee.type.slice(1);
    pdf.text(typeLabel, widthCm / 2, 7.2, { align: 'center' });

    // Add QR Code (if enabled)
    if (template.show_qr_code) {
        console.log('Adding QR code to front page');
        const qrSizeCm = 2.5;  // 2.5cm QR code
        const qrX = widthCm / 2 - qrSizeCm / 2;
        const qrY = 8.5;  // Start QR code at 8.5cm from top

        // Convert SVG QR code to image and add to PDF
        const qrDataUrl = svgToDataURL(data.qr_code_svg);
        console.log('QR code data URL length:', qrDataUrl.length);
        try {
            pdf.addImage(qrDataUrl, 'SVG', qrX, qrY, qrSizeCm, qrSizeCm);
            console.log('QR code added successfully');
        } catch (error) {
            console.error('Failed to add QR code:', error);
        }

        // Add QR UUID text below QR code for manual entry
        pdf.setFont(template.font_family || 'helvetica', 'normal');
        pdf.setFontSize(10);
        pdf.setTextColor(...hexToRGB(template.primary_color));
        pdf.text(attendee.qr_uuid, widthCm / 2, 11.2, { align: 'center' });

        // Add helper text
        pdf.setFontSize(8);
        pdf.setTextColor(100, 100, 100);
        pdf.text('Scan QR or enter code manually', widthCm / 2, 11.7, { align: 'center' });
        console.log('QR code UUID and helper text added');
    }
}

/**
 * Generate Back Side of Badge
 */
async function generateBadgeBack(pdf: any, data: BadgeData): Promise<void> {
    const { template, attendee, event } = data;
    const widthCm = template.badge_width_cm || 8.5;
    const heightCm = template.badge_height_cm || 12.5;

    console.log('Back page dimensions:', widthCm, 'cm x', heightCm, 'cm');

    // Draw background template if available
    if (template.back_template_url) {
        console.log('Loading back template:', template.back_template_url);
        try {
            await addImageToPDF(pdf, template.back_template_url, 0, 0, widthCm, heightCm);
            console.log('Back template loaded successfully');
        } catch (error) {
            console.error('Failed to load back template:', error);
            // Draw white background as fallback
            pdf.setFillColor(255, 255, 255);
            pdf.rect(0, 0, widthCm, heightCm, 'F');
            console.log('Using white background as fallback');
        }
    } else {
        console.log('No back template, using white background');
        // White background
        pdf.setFillColor(255, 255, 255);
        pdf.rect(0, 0, widthCm, heightCm, 'F');
    }

    // Add Event Info
    pdf.setFont(template.font_family || 'helvetica', 'bold');
    pdf.setFontSize(16);
    pdf.setTextColor(...hexToRGB(template.primary_color));
    pdf.text(event.name, widthCm / 2, 1.5, { align: 'center' });

    pdf.setFont(template.font_family || 'helvetica', 'normal');
    pdf.setFontSize(12);
    pdf.setTextColor(...hexToRGB(template.secondary_color));
    pdf.text(event.location, widthCm / 2, 2.5, { align: 'center' });
    pdf.text(event.date, widthCm / 2, 3.2, { align: 'center' });

    // Add Terms and Conditions if available
    if (template.terms_and_conditions) {
        pdf.setFont(template.font_family || 'helvetica', 'normal');
        pdf.setFontSize(10);
        pdf.setTextColor(60, 60, 60);

        const terms = pdf.splitTextToSize(template.terms_and_conditions, widthCm - 2);
        pdf.text(terms, widthCm / 2, heightCm / 2, { align: 'center', maxWidth: widthCm - 2 });
    }

    // Add Contact Info
    pdf.setFont(template.font_family || 'helvetica', 'normal');
    pdf.setFontSize(9);
    pdf.setTextColor(100, 100, 100);
    pdf.text('Email: ' + attendee.email, widthCm / 2, heightCm - 1, { align: 'center' });
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
 * Helper function to add image to PDF with aspect ratio preservation
 */
async function addImageToPDFWithAspectRatio(pdf: any, imageUrl: string, x: number, y: number, maxWidth: number, maxHeight: number): Promise<void> {
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
                // Calculate dimensions preserving aspect ratio
                const imgAspectRatio = img.width / img.height;
                const maxAspectRatio = maxWidth / maxHeight;

                let renderWidth = maxWidth;
                let renderHeight = maxHeight;

                if (imgAspectRatio > maxAspectRatio) {
                    // Image is wider than container
                    renderHeight = maxWidth / imgAspectRatio;
                } else {
                    // Image is taller than container
                    renderWidth = maxHeight * imgAspectRatio;
                }

                // Center the image in the allocated space
                const offsetX = x + (maxWidth - renderWidth) / 2;
                const offsetY = y + (maxHeight - renderHeight) / 2;

                // Detect image format from URL
                const format = imageUrl.toLowerCase().endsWith('.png') ? 'PNG' : 'JPEG';
                pdf.addImage(img, format, offsetX, offsetY, renderWidth, renderHeight);

                console.log(`Image loaded: ${img.width}x${img.height}, rendered as: ${renderWidth.toFixed(2)}x${renderHeight.toFixed(2)}`);
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
