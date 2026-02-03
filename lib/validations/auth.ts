import { z } from "zod";

export const loginSchema = z.object({
  email: z
    .string()
    .min(1, "Email is required")
    .email("Please enter a valid email"),
  password: z
    .string()
    .min(1, "Password is required")
    .min(8, "Password should be at least 8 characters long"),
});

export type LoginFormData = z.infer<typeof loginSchema>;

export const registerSchema = z.object({
  companyName: z
    .string()
    .min(1, "Company name is required")
    .min(2, "Company name must be at least 2 characters"),
  email: z
    .string()
    .min(1, "Email is required")
    .email("Please enter a valid email"),
  password: z
    .string()
    .min(1, "Password is required")
    .min(8, "Password should be at least 8 characters long"),
  accountType: z
    .string()
    .min(1, "Please select an account type"),
  acceptTerms: z
    .boolean()
    .refine((val) => val === true, "You must accept the Terms & Conditions"),
  acceptPrivacy: z
    .boolean()
    .refine((val) => val === true, "You must accept the Privacy Policy"),
});

export type RegisterFormData = z.infer<typeof registerSchema>;
