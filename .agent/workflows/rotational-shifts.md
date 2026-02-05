# Rotational Shifts Workflow

Rotational shifts allow you to automate employee schedules by cycling through a sequence of shifts over time. Unlike traditional scheduling, this system is **Shift-Driven** and requires zero manual enrollment.

## 1. Create a Rotation Plan
This is where you define the **Logic** and **Sequence** of the rotation.

### Configuration
- **Frequency Type**: How often the shift changes (Daily, Weekly, or Monthly).
- **Frequency Interval**: The multiplier for the frequency (e.g., Weekly + 2 = Every 2 weeks).
- **Start Date**: The "Day 0" for the calculation. All cycles are calculated relative to this date.

### Rotation Sequence
Define the **Order** of shifts to be cycled.
- **Example (Weekly Day/Night Swap)**:
  - Step 1: Morning Shift
  - Step 2: Night Shift

---

## 2. Automatic Enrollment (How it works)
You **do not need to assign employees** to a rotation plan. The system does this automatically:

1.  If an employee is assigned to a shift (e.g., "Morning Shift").
2.  And that shift is part of a rotation sequence (e.g., Step 1 of a plan).
3.  The system will **automatically** rotate that employee through the rest of the sequence over time.

> [!TIP]
> This creates **staggered coverage**. Employees on Morning move to Night, while employees already on Night move to the next step, ensuring all shifts are always covered.

---

## 3. Forecast Schedule
Use this tool to verify exactly what shift an employee will be on in the future.

1. Go to **Forecast Schedule**.
2. Select an **Employee**.
3. Select a **Date Range**.
4. The system will calculate their shifts and identify their **OFF DAYS**.

---

## 4. Manual Overrides (Daily Exceptions)
If an employee needs a one-day change (like an emergency swap) without breaking their rotation, create an **Override**. These take the highest priority.
